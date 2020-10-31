--	sms.lua
--	Part of FusionPBX
--	Copyright (C) 2010-2017 Mark J Crane <markjcrane@fusionpbx.com>
--	All rights reserved.
--
--	Redistribution and use in source and binary forms, with or without
--	modification, are permitted provided that the following conditions are met:
--
--	1. Redistributions of source code must retain the above copyright notice,
--	   this list of conditions and the following disclaimer.
--
--	2. Redistributions in binary form must reproduce the above copyright
--	   notice, this list of conditions and the following disclaimer in the
--	   documentation and/or other materials provided with the distribution.
--
--	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
--	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
--	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
--	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
--	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
--	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
--	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
--	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
--	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
--	POSSIBILITY OF SUCH DAMAGE.


-- lua app_custom.lua sms -f 123456789 -t 123456777 -m 'Hello World!' -s external

opthelp = [[
 -s, --source=OPTARG	Source of the message. Internal or external
 -d, --debug			Debug flag
 -f, --from=OPTARG		From. Mandatory in a case of external
 -t, --to=OPTARG		To. Mandatory in case of external
 -m, --message=OPTARG	Message. Optional in a case of external
]]

local function convert_pattern(pattern)
    
    -- Cleanup pattern-related magical characters
    local converted_pattern = pattern:gsub("%(", "%%(")
    converted_pattern = converted_pattern:gsub("%)", "%%)")
    converted_pattern = converted_pattern:gsub("%%", "%%%%")
    converted_pattern = converted_pattern:gsub("%.", "%%.")
    converted_pattern = converted_pattern:gsub("%[", "%%[")
    converted_pattern = converted_pattern:gsub("%]", "%%]")
    converted_pattern = converted_pattern:gsub("%+", "%%+")
    converted_pattern = converted_pattern:gsub("%-", "%%-")
    converted_pattern = converted_pattern:gsub("%?", "%%?")

    -- Internal convention x - any digit, * - any number of digits
    converted_pattern = converted_pattern:gsub("x", "%%d")
    converted_pattern = converted_pattern:gsub("%*", ".*")

    return converted_pattern

end

local function save_sms_to_database(db, params)
	local sql = "INSERT INTO v_sms_messages "
	sql = sql .."( "
	sql = sql .."domain_uuid, "
	sql = sql .."sms_message_uuid, "
	sql = sql .."sms_message_timestamp, "
	sql = sql .."sms_message_from, "
	sql = sql .."sms_message_to, "
	sql = sql .."sms_message_direction, "
	sql = sql .."sms_message_text, "
	sql = sql .."sms_message_status "
	sql = sql ..") "
	sql = sql .."VALUES ( "
	sql = sql ..":domain_uuid, "
	sql = sql ..":sms_message_uuid, "
	sql = sql .."now(), "
	sql = sql ..":sms_message_from, "
	sql = sql ..":sms_message_to, "
	sql = sql ..":sms_message_direction, "
	sql = sql ..":sms_message_text, "
	sql = sql ..":sms_message_status "
	sql = sql ..")"

	--run the query
	db:query(sql, params)
end

local function number_translate(number, translate_profile) 
	if (not translate_profile or #translate_profile == 0) then
		return number
	end

	cmd = "translate " .. number .. " " .. translate_profile

	translated_number = trim(api:executeString(cmd)) or ""

	if #translated_number > 0 then
		return translated_number
	end

	return number
end

local function text_cleanup(text)
	
	local converted_text = 	   text:gsub("\'", "`")
	converted_text = converted_text:gsub("’", "`")
	converted_text = converted_text:gsub("‘", "`")

	converted_text = converted_text:gsub("“", "\"")
	converted_text = converted_text:gsub("”", "\"")

	return converted_text
end

local log = require "resources.functions.log".sms

local Settings = require "resources.functions.lazy_settings"
local Database = require "resources.functions.database"
require "resources.functions.trim";

api = freeswitch.API();

local db = dbh or Database.new('system')
--exits the script if we didn't connect properly
assert(db:connected())

opts, args, err = require('app.custom.functions.optargs').from_opthelp(opthelp, argv)

if opts == nil then
	log.error("Options are not parsable " .. err)

	message:chat_execute("stop")
    do return end
end

local sms_source = opts.s or 'internal'

if sms_source == 'internal' then
	if opts.d then log.info("Message source is internal. Saving to database") end

	uuid               = message:getHeader("Core-UUID")
	from_user          = message:getHeader("from_user")
	from_domain        = message:getHeader("from_host")
	to_user            = message:getHeader("to_user")
	to_domain          = message:getHeader("to_host") or from_domain
	content_type       = message:getHeader("type")
	sms_message_text   = message:getBody()

	--Clean body up for Groundwire send
	local sms_message_text_raw = sms_message_text
	local _, sms_temp_end = string.find(sms_message_text_raw, 'Content%-length:')
	if sms_temp_end == nil then
		sms_message_text = sms_message_text_raw
	else
		_, sms_temp_end = string.find(sms_message_text_raw, '\r\n\r\n', sms_temp_end)
		if sms_temp_end == nil then
			sms_message_text = sms_message_text_raw
		else
			sms_message_text = string.sub(sms_message_text_raw, sms_temp_end + 1)
		end
	end

	sms_message_text = sms_message_text:gsub('%"','')
	sms_type      	 = 'sms'

	-- Getting from/to user data
	local domain_uuid
	if (from_user and from_domain) then
		sms_message_from   = from_user .. '@' .. from_domain
		-- Getting domain_uuid
		cmd = "user_data ".. from_user .. "@" .. from_domain .. " var domain_uuid"
		domain_uuid = trim(api:executeString(cmd))
		-- Getting from_user_exists
		cmd = "user_exists id ".. from_user .." "..from_domain
		from_user_exists = api:executeString(cmd)
	else 
		log.error("From user or from domain is not existed. Cannot process this message as internal")

		message:chat_execute("stop")
		do return end
	end

	if opts.d then log.notice("From user exists: " .. from_user_exists) end

	if (to_user and to_domain) then
		sms_message_to     = to_user .. '@' .. to_domain

		cmd = "user_exists id ".. to_user .." "..to_domain
		to_user_exists = api:executeString(cmd)
	else
		to_user_exists = 'false'
	end
	-- End getting from/to user data
	
	if (from_user_exists == 'false') then
		log.error("From user is not exists. Cannot process this request")

		message:chat_execute("stop")
		do return end
	end

	if not domain_uuid then
		log.error("Please make sure " .. domain_name .. " is existed on the system")

		message:chat_execute("stop")
		do return end
	end

	-- Get settings
	local settings = Settings.new(db, from_domain, domain_uuid)

	if (to_user_exists == 'true') then

		--set the parameters for database save
		local params= {
			domain_uuid = domain_uuid,
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = sms_message_to,
			sms_message_direction = 'send',
			sms_message_status = 'Sent. Local',
			sms_message_text = sms_message_text,
		}

		save_sms_to_database(db, params)

		message:chat_execute("stop")
		do return end
	end

	-- SMS to external

	if not to_user then
		local params= {
			domain_uuid = domain_uuid,
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = "NA",
			sms_message_direction = 'send',
			sms_message_status = 'Error. No TO user specified',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.error('To user is empty. Discarding sent')

		message:chat_execute("stop")
		do return end
	end

	-- Get routing rules for this message type.
	sql =        "SELECT sms_routing_source, "
	sql = sql .. "sms_routing_destination, "
	sql = sql .. "sms_routing_target_details, "
	sql = sql .. "sms_routing_number_translation_source, "
	sql = sql .. "sms_routing_number_translation_destination "
	sql = sql .. " FROM v_sms_routing WHERE"
	sql = sql .. " domain_uuid = :domain_uuid"
	sql = sql .. " AND sms_routing_target_type = 'carrier'"
	sql = sql .. " AND sms_routing_enabled = 'true'"

	local params = {
		domain_uuid = domain_uuid
	}

	local routing_patterns = {}
	db:query(sql, params, function(row)
		table.insert(routing_patterns, row)
		if opts.d then log.info("Adding carrier " .. row['sms_routing_target_details'] .. "to pool") end
	end)
	
	local sms_carrier
	local sms_routing_number_translation_source
	local sms_routing_number_translation_destination

	if (#routing_patterns == 0) then

		local params= {
			domain_uuid = domain_uuid,
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = to_user,
			sms_message_direction = 'send',
			sms_message_status = 'Error. No routing patterns',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.notice("External routing table is empty. Exiting.")

		message:chat_execute("stop")
		do return end
	end

	for _, routing_pattern in pairs(routing_patterns) do
		sms_routing_source = routing_pattern['sms_routing_source']
		sms_routing_destination = routing_pattern['sms_routing_destination']

		if opts.d then log.info("Testing F:" .. from_user .. " -> " .. sms_routing_source .. " and  D:" .. to_user .. " -> " .. sms_routing_destination) end

		sms_routing_source      = convert_pattern(sms_routing_source:lower())
		sms_routing_destination = convert_pattern(sms_routing_destination:lower())

		if (from_user:find(sms_routing_source) and to_user:find(sms_routing_destination)) then
			
			sms_carrier = routing_pattern['sms_routing_target_details']
			sms_routing_number_translation_source = routing_pattern['sms_routing_number_translation_source']
			sms_routing_number_translation_destination = routing_pattern['sms_routing_number_translation_destination']

			if opts.d then log.notice("Using " .. sms_carrier .. " for this SMS") end
			break
		end
	end

	if (not sms_carrier) then

		local params= {
			domain_uuid = domain_uuid,
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = to_user,
			sms_message_direction = 'send',
			sms_message_status = 'Error. No carrier found',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.warning("Cannot find carrier for this SMS: From:" .. sms_message_from .. "  To: " .. sms_message_to)
		
		message:chat_execute("stop")
		do return end
	end

	local sms_request_type = settings:get('sms', sms_carrier .. '_request_type', 'text')
	local sms_carrier_url = settings:get('sms', sms_carrier .. "_url", 'text')
	local sms_carrier_user = settings:get('sms', sms_carrier .. "_user", 'text')
	local sms_carrier_password = settings:get("sms", sms_carrier .. "_password", 'text')
	local sms_carrier_body = settings:get("sms", sms_carrier .. "_body", "text")
	local sms_carrier_content_type = settings:get("sms", sms_carrier .. "_content_type", "text") or "application/json"
	local sms_carrier_method =  settings:get("sms", sms_carrier .. "_method", "text") or 'post'

	--get the sip user outbound_caller_id
	cmd = "user_data " .. sms_message_from .. " var outbound_caller_id_number"
	caller_id_from = trim(api:executeString(cmd)) or from_user

	--Do from/to modifications
	caller_id_from = number_translate(caller_id_from, sms_routing_number_translation_source)

	to_user = number_translate(to_user, sms_routing_number_translation_destination)

	-- Cleanup text
	sms_message_text = text_cleanup(sms_message_text)

	--replace variables for their value
	if (sms_carrier_url) then
		sms_carrier_url = sms_carrier_user and sms_carrier_url:gsub("${user}", sms_carrier_user) or sms_carrier_url
		sms_carrier_url = sms_carrier_password and sms_carrier_url:gsub("${password}", sms_carrier_password) or sms_carrier_url
		sms_carrier_url = sms_carrier_url:gsub("${from}", caller_id_from)
		sms_carrier_url = sms_carrier_url:gsub("${to}", to_user)
		sms_carrier_url = sms_carrier_url:gsub("${text}", sms_message_text)
	else 
		log.warning("Cannot find carrier url for " .. sms_carrier)

		message:chat_execute("stop")
		do return end
	end

	if (sms_carrier_body) then
		sms_carrier_body = sms_carrier_user and sms_carrier_body:gsub("${user}", sms_carrier_user) or sms_carrier_body
		sms_carrier_body = sms_carrier_password and sms_carrier_body:gsub("${password}", sms_carrier_password) or sms_carrier_body
		sms_carrier_body = sms_carrier_body:gsub("${from}", caller_id_from)
		sms_carrier_body = sms_carrier_body:gsub("${to}", to_user)
		sms_carrier_body = sms_carrier_body:gsub("${text}", sms_message_text)
	else 
		sms_carrier_body = ""
	end

	-- Send to the provider using curl
	cmd = "curl " .. sms_carrier_url .. " content-type " .. sms_carrier_content_type .. " " .. sms_carrier_method .. " " .. sms_carrier_body

	if opts.d then log.info("Using CURL command " .. cmd) end

	api:executeString(cmd)

	local params= {
		domain_uuid = domain_uuid,
		sms_message_uuid = api:executeString("create_uuid"),
		sms_message_from = sms_message_from,
		sms_message_to = to_user,
		sms_message_direction = 'send',
		sms_message_status = 'Sent. ' .. sms_carrier ,
		sms_message_text = sms_message_text,
	}
	save_sms_to_database(db, params)


--- External SMS routing
elseif sms_source == 'external' then

	if opts.d then log.info("Message source is external. Saving to database and preforming routing") end

	if not opts.f or not opts.t or not opts.m then
		log.warning("From/To/Message is not specified, aborting")

		local params= {
			domain_uuid = '',
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = opts.f or "NA",
			sms_message_to = opts.t or "NA",
			sms_message_direction = 'receive',
			sms_message_status = 'Error. No mandatory fields found',
			sms_message_text = opts.m or "NA",
		}
		save_sms_to_database(db, params)

		do return end
	end

	sms_message_from = opts.f
	sms_message_to = opts.t
	sms_message_text = opts.m

	-- Get routing rules for this message type.
	sql =        "SELECT domain_uuid, "
	sql = sql .. "sms_routing_source, "
	sql = sql .. "sms_routing_destination, "
	sql = sql .. "sms_routing_target_details, "
	sql = sql .. "sms_routing_number_translation_source, "
	sql = sql .. "sms_routing_number_translation_destination "
	sql = sql .. " FROM v_sms_routing WHERE"
	sql = sql .. " sms_routing_target_type = 'internal'"
	sql = sql .. " AND sms_routing_enabled = 'true'"

	local params = {
		sms_routing_source = opts.f
	}

	local routing_patterns = {}
	db:query(sql, params, function(row)
		table.insert(routing_patterns, row)
		if opts.d then log.info("Adding internal destination " .. row['sms_routing_target_details'] .. " to pool") end
	end)

	if (#routing_patterns == 0) then

		local params= {
			domain_uuid = '',
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = sms_message_to,
			sms_message_direction = 'receive',
			sms_message_status = 'Error. No routing patterns',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.notice("External routing table is empty. Exiting.")

		message:chat_execute("stop")
		do return end
	end

	local domain_uuid
	local to_domain
	local sms_routing_number_translation_source
	local sms_routing_number_translation_destination

	for _, routing_pattern in pairs(routing_patterns) do
		sms_routing_source = routing_pattern['sms_routing_source']
		sms_routing_destination = routing_pattern['sms_routing_destination']

		if opts.d then log.info("Testing F:" .. sms_message_from .. " -> " .. sms_routing_source .. " and  D:" .. sms_message_to .. " -> " .. sms_routing_destination) end

		sms_routing_source      = convert_pattern(sms_routing_source:lower())
		sms_routing_destination = convert_pattern(sms_routing_destination:lower())

		if (sms_message_from:find(sms_routing_source) and sms_message_to:find(sms_routing_destination)) then
			
			domain_uuid = routing_pattern['domain_uuid']
			sms_routing_number_translation_source = routing_pattern['sms_routing_number_translation_source']
			sms_routing_number_translation_destination = routing_pattern['sms_routing_number_translation_destination']
			to_user = routing_pattern['sms_routing_target_details']

			if opts.d then log.notice("Using domain uuid  " .. domain_uuid .. " for this SMS") end
			break
		end
	end

	if domain_uuid then
		-- Get domain_name by UUID
		sql =        "SELECT domain_name "
		sql = sql .. " FROM v_domains WHERE"
		sql = sql .. " domain_uuid = :domain_uuid"
		sql = sql .. " AND domain_enabled = 'true'"

		local params = {
			domain_uuid = domain_uuid,
		}

		db:query(sql, params, function(row)
			to_domain = row['domain_name']
			if opts.d then log.info("Domain name " .. to_domain .. " found") end
		end)
	end

	if not to_domain then

		local params= {
			domain_uuid = domain_uuid or '',
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from,
			sms_message_to = sms_message_to,
			sms_message_direction = 'receive',
			sms_message_status = 'Error. No domain name found',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.notice("Could not find routing rules for this SMS. Exiting.")

		do return end
	end

	from_user = number_translate(sms_message_from, sms_routing_number_translation_source)

	cmd = "user_exists id ".. to_user .." "..to_domain
	to_user_exists = api:executeString(cmd)
	

	if (to_user_exists ~= 'true') then
		local params= {
			domain_uuid = domain_uuid,
			sms_message_uuid = api:executeString("create_uuid"),
			sms_message_from = sms_message_from .. "(" .. from_user .. ")",
			sms_message_to = sms_message_to .. "(" .. to_user .. ")",
			sms_message_direction = 'receive',
			sms_message_status = 'Error. User to deliver is not found',
			sms_message_text = sms_message_text,
		}
		save_sms_to_database(db, params)

		log.notice("To user is not exists. Exiting.")

		do return end
	end

	-- Sending message
	sms_message_to_normalized = number_translate(sms_message_to, sms_routing_number_translation_destination)

	local event = freeswitch.Event("CUSTOM", "SMS::SEND_MESSAGE");
		event:addHeader("proto", "sip");
		event:addHeader("dest_proto", "sip");
		event:addHeader("from", from_user)
		event:addHeader("from_user", from_user);
		event:addHeader("from_host", to_domain);
		event:addHeader("from_full", "sip:" .. from_user .."@".. to_domain);
		event:addHeader("to", to_user .. "@".. to_domain);
		event:addHeader("to_user", to_user);
		event:addHeader("to_host", to_domain)
		event:addHeader("subject", sms_message_to_normalized);
		event:addHeader("replying", "true");
		event:addHeader("sip_profile", "internal");
		event:addHeader("type", "text/plain");
		event:addBody(sms_message_text);
	
	if opts.d then
		log.notice(event:serialize())
	end
    
	event:fire();
	   
	local params= {
		domain_uuid = domain_uuid,
		sms_message_uuid = api:executeString("create_uuid"),
		sms_message_from = sms_message_from .. "(" .. from_user .. ")",
		sms_message_to = sms_message_to .. "(" .. to_user .. ")",
		sms_message_direction = 'receive',
		sms_message_status = 'Sent',
		sms_message_text = sms_message_text,
	}
	save_sms_to_database(db, params)

	log.notice("Message sent to " .. to_user .. '@' .. to_domain)

else 
	log.warning("[sms] Source " .. sms_source .. " is not yet implemented")
end