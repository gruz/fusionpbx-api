--
--	FusionPBX
--	Version: MPL 1.1
--
--	The contents of this file are subject to the Mozilla Public License Version
--	1.1 (the "License"); you may not use this file except in compliance with
--	the License. You may obtain a copy of the License at
--	http://www.mozilla.org/MPL/
--
--	Software distributed under the License is distributed on an "AS IS" basis,
--	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
--	for the specific language governing rights and limitations under the
--	License.
--
--	The Original Code is FusionPBX
--
--	The Initial Developer of the Original Code is
--	Mark J Crane <markjcrane@fusionpbx.com>
--	Copyright (C) 2010-2014
--	the Initial Developer. All Rights Reserved.
--
--	Contributor(s):
--	Salvatore Caruso <salvatore.caruso@nems.it>
--	Riccardo Granchi <riccardo.granchi@nems.it>

--include config.lua
	require "resources.functions.config"

if (session:ready()) then
	fifo_simo = session:getVariable("fifo_simo") or '1'
	fifo_timeout = session:getVariable("fifo_timeout") or '10'
	fifo_lag = session:getVariable("fifo_lag") or '10'
	fifo_exit_key = session:getVariable("fifo_exit_key")

	extension_queue = session:getVariable("extension_queue");
	extension = string.sub(extension_queue, string.len("queue_") + 1 )

	-- freeswitch.consoleLog("notice", "Extension Queue [" .. extension_queue .. "]\n");

	api = freeswitch.API()
	fifo_count = api:executeString("fifo count " .. extension_queue)

	-- freeswitch.consoleLog("notice", "fifo count " .. fifo_count .. "]\n");

	-- Parsing queue info
	i = 0;
	v = {};
	for w in string.gmatch(fifo_count,"[^:]+") do
		v[i] = w
		i = i + 1
	end

	fifo_name = v[0]
	consumer_count = v[1]
	caller_count = v[2]
	member_count = v[3]
	ring_consumer_count = v[4]
	idle_consumer_count = v[5]

	if( not (member_count == "0") ) then
		freeswitch.consoleLog("notice", "Adding member [" .. extension .. "] to fifo " .. extension_queue .. " \n")

		session:execute("set", "fifo_member_add_result=${fifo_member(add " .. extension_queue .." {fifo_member_wait=nowait}user/" .. extension .. " " ..fifo_simo .. " " ..fifo_timeout .. " " .. fifo_lag .. "} )") --simo timeout lag
	end;

	-- Answering the call
	if (fifo_exit_key) then
		fifo_exit_destination = session:getVariable("fifo_exit_destination") or "*99" .. session:getVariable("called_extension")
		if (fifo_exit_destination == 'voicemail') then
			fifo_exit_destination = "*99" .. session:getVariable("called_extension")
		end
		context = session:getVariable("context") or session:getVariable("domain_name")
		
		session:execute("bind_digit_action", extension_queue .. "," .. fifo_exit_key .. ",exec:transfer," .. fifo_exit_destination .. " XML " .. context)
		session:execute("digit_action_set_realm", extension_queue)
	end
	session:answer()
	session:execute( "fifo", extension_queue .. " in" )
end
