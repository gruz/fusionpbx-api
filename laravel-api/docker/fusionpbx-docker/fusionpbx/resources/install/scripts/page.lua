--	page.lua
--	Part of FusionPBX
--	Copyright (C) 2010 Mark J Crane <markjcrane@fusionpbx.com>
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

--set default settings
	pin_number = "";
	max_tries = "3";
	digit_timeout = "3000";

--define the trim function
	require "resources.functions.trim";

--define the explode function
	require "resources.functions.explode";

--define the split function
	require "resources.functions.split";
	
--define database connection
	require "resources.functions.config"
	require "resources.functions.database_handle"
	local dbh = database_handle('switch');

--iterator over numbers.
	local function each_number(value)
		local begin_value, end_value = split_first(value, "-", true)
		if (not end_value) or (begin_value == end_value) then
			return function()
				local result = begin_value
				begin_value = nil
				return result
			end
		end

		if string.find(begin_value, "^0") then
			assert(#begin_value == #end_value, "number in range with leading `0` should have same length")
		end

		local number_length = ("." .. tostring(#begin_value))
		begin_value, end_value = tonumber(begin_value), tonumber(end_value)
		assert(begin_value and end_value and (begin_value <= end_value), "Invalid range: " .. value)

		return function()
			value, begin_value = begin_value, begin_value + 1
			if value > end_value then return end
			return string.format("%" .. number_length .. "d", value)
		end
	end

	-- Function to get if member is available. 
	local function member_available(member)

		local member_full_name = member .. '@' .. domain_name

		-- Check if user exists at all.
		local user_exists = api:executeString('sofia_contact ' .. member_full_name)
		if (user_exists == 'error/user_not_registered') then
			freeswitch.consoleLog("NOTICE", "[page] destination " .. member_full_name .. " is not registered or exist\n")
			return false
		end

		-- Skip talking users
		if (not page_skip_active_users) then
			freeswitch.consoleLog("NOTICE", "[page] destination " .. member_full_name .. " available\n")
			return true
		end

		--local sql = "SELECT state, name, cid_name, cid_num, presence_id FROM channels"
		local sql = "SELECT state FROM channels"
		sql = sql .. " WHERE application_data LIKE '%" .. member_full_name .. "%'"
		sql = sql .. " OR name LIKE '%" .. member_full_name .. "%'"
		sql = sql .. " OR presence_id = '" .. member_full_name .. "'"
		sql = sql .. " LIMIT 1" -- We don't need more than 1 result at all
		
		dbh:query(sql, function(row)
            chan_state = row.state
		end)
		
		if chan_state == nil then
			chan_state = (trim(api:executeString("show channels like " .. member_full_name)) ~= '0 total.') and 'active' or false
		end

		if (chan_state) then
			freeswitch.consoleLog("NOTICE", "[page] destination " .. member_full_name .. " is in " .. chan_state .. " state\n")
			return false
		end
		freeswitch.consoleLog("NOTICE", "[page] destination " .. member_full_name .. " available\n")
		return true
	end

--make sure the session is ready
	if ( session:ready() ) then
		--answer the call
			session:answer();
		--get the dialplan variables and set them as local variables
			destination_number = session:getVariable("destination_number")
			pin_number = session:getVariable("pin_number")
			domain_name = session:getVariable("domain_name")
			sounds_dir = session:getVariable("sounds_dir")
			destinations = session:getVariable("destinations") or session:getVariable("extension_list")
			rtp_secure_media = session:getVariable("rtp_secure_media") or 'false'
			page_skip_active_users = (session:getVariable("page_skip_active_users") == 'true') and true or false
			caller_id_name = session:getVariable("caller_id_name")
			caller_id_number = session:getVariable("caller_id_number")
			sip_from_user = session:getVariable("sip_from_user")
			mute = session:getVariable("mute")
		--set the sounds path for the language, dialect and voice
			default_language = session:getVariable("default_language") or 'en'
			default_dialect = session:getVariable("default_dialect") or 'us'
			default_voice = session:getVariable("default_voice") or 'callie'

			destination_table = explode(",",destinations)
			
		--define the conference name
			local conference_name = "page-"..destination_number.."@"..domain_name.."@page"

		--set the caller id
			if (not caller_id_name) then
				--caller id name provided do nothing
				effective_caller_id_name = session:getVariable("effective_caller_id_name");
				caller_id_name = effective_caller_id_name;
			end

			if (not caller_id_number) then
				effective_caller_id_number = session:getVariable("effective_caller_id_number");
				caller_id_number = effective_caller_id_number;
			end

		--set conference flags
			if (mute == "true") then
				flags = "flags{mute}";
			else
				flags = "flags{}";
			end

		--if the pin number is provided then require it
			if (pin_number) then
				--sleep
					session:sleep(500);
				--get the user pin number
					min_digits = 2;
					max_digits = 20;
					digits = session:playAndGetDigits(min_digits, max_digits, max_tries, digit_timeout, "#", "phrase:voicemail_enter_pass:#", "", "\\d+");
				--validate the user pin number
					pin_number_table = explode(",",pin_number);
					for index,pin_number in pairs(pin_number_table) do
						if (digits == pin_number) then
							--set the variable to true
								auth = true;
							--set the authorized pin number that was used
								session:setVariable("pin_number", pin_number);
							--end the loop
								break;
						end
					end
				--if not authorized play a message and then hangup
					if (not auth) then
						session:streamFile("phrase:voicemail_fail_auth:#");
						session:hangup("NORMAL_CLEARING");
						return;
					end
			end

		--originate the calls
			destination_count = 0;
			api = freeswitch.API();
			for index,value in pairs(destination_table) do
				for destination in each_number(value) do

					--get the destination required for number-alias
					destination = api:execute("user_data", destination .. "@" .. domain_name .. " attr id");

					--prevent calling the user that initiated the page and to non-existing destinations
					if ((sip_from_user ~= destination) and #destination ~= 0 and member_available(destination)) then
						--originate the call
						cmd_string = "bgapi originate {sip_auto_answer=true,sip_h_Alert-Info='Ring Answer',hangup_after_bridge=false,rtp_secure_media="..rtp_secure_media..",origination_caller_id_name='"..caller_id_name.."',origination_caller_id_number="..caller_id_number.."}user/"..destination.."@"..domain_name.." conference:"..conference_name.."+"..flags.." inline";
						api:executeString(cmd_string);
						destination_count = destination_count + 1;
					end
				end
			end

		--send main call to the conference room
			if (destination_count > 0) then
				if (session:getVariable("moderator") == "true") then
					moderator_flag = ",moderator";
				else
					moderator_flag = "";
				end
				session:execute("conference", conference_name.."+flags{endconf"..moderator_flag.."}");
			else
				session:execute("playback", "tone_stream://%(500,500,480,620);loops=3");
			end

	end
