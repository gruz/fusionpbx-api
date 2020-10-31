--	FusionPBX
--	Version: MPL 1.1

--	The contents of this file are subject to the Mozilla Public License Version
--	1.1 (the "License"); you may not use this file except in compliance with
--	the License. You may obtain a copy of the License at
--	http://www.mozilla.org/MPL/

--	Software distributed under the License is distributed on an "AS IS" basis,
--	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
--	for the specific language governing rights and limitations under the
--	License.

--	The Original Code is FusionPBX

--	The Initial Developer of the Original Code is
--	Mark J Crane <markjcrane@fusionpbx.com>
--	Portions created by the Initial Developer are Copyright (C) 2016
--	the Initial Developer. All Rights Reserved.

-- load config
	require "resources.functions.config";

--set debug
	-- debug["sql"] = true;

--load libraries
	local log = require "resources.functions.log"["app:dialplan:outbound:speed_dial"]
	local Database = require "resources.functions.database";
	local cache = require "resources.functions.cache";
	local json = require "resources.functions.lunajson";

--get the variables
	domain_name = session:getVariable("domain_name");
	domain_uuid = session:getVariable("domain_uuid");
	context = session:getVariable("context");
	user = session:getVariable("sip_auth_username")
		or session:getVariable("username");

--get the argv values
	destination = argv[2];

-- search in memcache first
	local key = "app:dialplan:outbound:speed_dial:" .. user .. ":" .. destination .. "@" .. domain_name
	local source = "memcache"
	local value = cache.get(key)

-- decode value from memcache
	if value then
		local t = json.decode(value)
		if not (t and t.phone_number) then
			log.warningf("can not decode value from memcache: %s", value)
			value = nil
		else
			value = t
		end
	end

-- search in database
	if not value then
		-- set source flag
			source = "database"

		-- connect to database
			local dbh = Database.new('system');

		-- search for the phone number in database using the speed dial
			local sql = [[
				-- find all contacts with correct user or without users and groups at all
				select t0.phone_number --, t6.extension, 'GROUP:' || t3.group_name as user_name
				from v_contact_phones t0
				inner join v_contacts t1 on t0.contact_uuid = t1.contact_uuid
				left outer join v_contact_groups t2 on t1.contact_uuid = t2.contact_uuid
				left outer join v_group_users t3 on t2.group_uuid = t3.group_uuid
				left outer join v_users t4 on t3.user_uuid = t4.user_uuid
				left outer join v_extension_users t5 on t4.user_uuid = t5.user_uuid
				left outer join v_extensions t6 on t5.extension_uuid = t6.extension_uuid
				where t0.domain_uuid = :domain_uuid and t0.phone_speed_dial = :phone_speed_dial
					and ( (1 = 0)
						or (t6.domain_uuid = :domain_uuid and (t6.extension = :user or t6.number_alias = :user))
						or (t2.contact_uuid is null and not exists(select 1 from v_contact_users t where t.contact_uuid = t0.contact_uuid) )
					)

				union

				-- find all contacts with correct group or withot users and groups at all
				select t0.phone_number -- , t5.extension, 'USER:' || t3.username as user_name
				from v_contact_phones t0
				inner join v_contacts t1 on t0.contact_uuid = t1.contact_uuid
				left outer join v_contact_users t2 on t1.contact_uuid = t2.contact_uuid
				left outer join v_users t3 on t2.user_uuid = t3.user_uuid
				left outer join v_extension_users t4 on t3.user_uuid = t4.user_uuid
				left outer join v_extensions t5 on t4.extension_uuid = t5.extension_uuid
				where t0.domain_uuid = :domain_uuid and t0.phone_speed_dial = :phone_speed_dial
					and ( (1 = 0)
						or (t5.domain_uuid = :domain_uuid and (t5.extension = :user or t5.number_alias = :user))
						or (t2.contact_user_uuid is null and not exists(select 1 from v_contact_groups t where t.contact_uuid = t0.contact_uuid))
					)
			]];
			local params = {phone_speed_dial = destination, domain_uuid = domain_uuid, user = user};

			if (debug["sql"]) then
				log.noticef("SQL: %s; params: %s", sql, json.encode(params));
			end

			local phone_number = dbh:first_value(sql, params)

		-- release database connection
			dbh:release()

		-- set the cache
			if phone_number then
				value = {phone_number = phone_number}
				cache.set(key, json.encode(value), expire["speed_dial"])
			end
	end

-- transfer
	if value then
		--log the result
			log.noticef("%s XML %s source: %s", destination, context, source)

		--transfer the call
			session:transfer(value.phone_number, "XML", context);
	else
		log.warningf('can not find number: %s in domain: %s', destination, domain_name)
	end
