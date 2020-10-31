-- Select a default gateway for domain and choose it for outbound dialing

-- connect to the database
require "resources.functions.database_handle"

local dbh = database_handle('system');

if (session:ready()) then

    local sql = "";
    local domain_id = session:getVariable("domain_uuid")

    if (domain_id == nil) then
        domain_id = session:getVariable("domain_name") or session:getVariable("sip_invite_domain")
    else
        sql = "SELECT gateway_uuid FROM v_gateways "
        sql = sql .. "WHERE domain_uuid = '"..domain_id.."' "
        sql = sql .. "AND enabled = 'true'"
    end

    if (domain_id ~= nil) then
        if (sql == "") then
            sql = "SELECT gateway_uuid FROM v_gateways "
            sql = sql .. "WHERE domain_uuid = "
            sql = sql .. "(SELECT domain_uuid FROM v_domains "
            sql = sql .. "WHERE domain_name = '"..domain_id.."' "
            sql = sql .. "and domain_enabled = 'true') "
            sql = sql .. "AND enabled = 'true'"
        end
        local results_count = 0
        dbh:query(sql, function(row)
            gateway_uuid = row["gateway_uuid"] and row["gateway_uuid"] or nil
            results_count = results_count + 1
        end);
        
        dbh:release()

        if (gateway_uuid ~= nil and results_count == 1) then
            freeswitch.consoleLog("NOTICE", "[dial_default_gateway] Dialing through gateway "..gateway_uuid.."\n");
            local callee_id_number = session:getVariable("callee_id_number")
            callee_id_number = callee_id_number and callee_id_number or "" 
            session:execute("bridge", "{sip_cid_type=none}sofia/gateway/" .. gateway_uuid .. "/" .. callee_id_number)
        else
            freeswitch.consoleLog("NOTICE", "[dial_default_gateway] Cannot get gateway for domain("..results_count..")\n");
        end
    else
        dbh:release()
        freeswitch.consoleLog("NOTICE", "[dial_default_gateway] Cannot get domain_uuid or domain_id\n");
        session:execute('info')
    end

else 
    dbh:release()
end