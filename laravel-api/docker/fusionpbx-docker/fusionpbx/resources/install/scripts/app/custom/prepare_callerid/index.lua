-- Set effective_caller_id name and number regarding different rules, lise outbound and also..
-- Point - you have to define company_caller_id to use this function

if ( session:ready() ) then

    arguments = "";
    for key,value in pairs(argv) do
        if (key > 1) then
            arguments = arguments .. " '" .. value .. "'";
            freeswitch.consoleLog("notice", "[prepare_callerid.lua] Modifiers: argv["..key.."]: "..argv[key].."\n");
        end
    end

   modifier_1 = argv[2] or '';

    company_caller_id = session:getVariable("company_caller_id")
    if (company_caller_id) then

        outbound_caller_id_number = session:getVariable("outbound_caller_id_number") or ""

        caller_id_number = session:getVariable("caller_id_number") or ""

        outbound_caller_id_name = session:getVariable("outbound_caller_id_name") or ""

        session:consoleLog("notice","[PREPARE_CALLERID]: company_caller_id: "..company_caller_id.." outbound_caller_id_number: "..outbound_caller_id_number.." outbound_caller_id_name:"..outbound_caller_id_name.." caller_id_number:"..caller_id_number.."\n")
        outbound_caller_id_name = string.lower(outbound_caller_id_name)

        if (string.len(caller_id_number) < 5) then
            if (string.find(outbound_caller_id_name, 'anon') or outbound_caller_id_number == '0000') then -- Checking for anon callerid
                effective_caller_id = "restricted|"..company_caller_id
                effective_caller_id_sip_header = "restricted"
            else
        	if (modifier_1 == 'a2billing') then
                    if (string.len(outbound_caller_id_number) > 3) then
                        effective_caller_id = outbound_caller_id_number.."|"..company_caller_id
                    else
                        effective_caller_id = company_caller_id.."|"..company_caller_id
                    end
                else
                    effective_caller_id = (string.len(outbound_caller_id_number) > 1) and outbound_caller_id_number or company_caller_id
                end
            end
        else
            effective_caller_id = caller_id_number
        end
        
        -- We're using original caller_id_number. Make sure it's with leading "+"
        if (string.sub(effective_caller_id, 1, 1) ~= "+" and string.sub(effective_caller_id, 1, 1) ~= "r") then
            effective_caller_id = "+" .. effective_caller_id
        end

        effective_caller_id_sip_header = effective_caller_id_sip_header or effective_caller_id

        session:setVariable("sip_h_X-ASTPP-Outbound", effective_caller_id_sip_header)
        session:setVariable("sip_h_X-ASTPP-Billing", company_caller_id)
        -- else
        session:consoleLog("notice","[PREPARE_CALLERID]: Setting effective_caller_id_name to "..effective_caller_id.."\n")
        session:setVariable("effective_caller_id_name", effective_caller_id)
        -- end

    else
        session:consoleLog("notice","[PREPARE_CALLERID]: company_caller_id is empty. Doing nothing\n")
    end
end