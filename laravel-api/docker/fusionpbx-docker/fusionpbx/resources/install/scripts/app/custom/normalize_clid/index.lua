-- Set effective_caller_id name and number regarding different rules, lise outbound and also..

function getFusionCompanyCallerid(prepend_prefix, append_ani)

    caller_id = session:getVariable("company_caller_id")
    caller_id = caller_id:gsub("%D", "")

    if caller_id and #caller_id > 0 then
        return prepend_prefix .. caller_id .. append_ani
    end

    return nil
end

function setCallerID(name, number)
    session:setVariable("effective_caller_id_name", name)
    session:setVariable("effective_caller_id_number", number)
end

function fsLog(message)
    if opts.v then
        freeswitch.consoleLog("notice", "[NORMALIZE CALLERID] " .. message .. "\n")
    end
end


opthelp = [[
 -t, --trunk=OPTARG                 Trunk (provider) used. Could be Telgo or ASTPP4. Default is ASTPP4
 -a, --append-ani=OPTARG            Append ANI to end of the call. "yes" by default
 -p, --prepend-prefix=OPTARG        Prepend prefix. Empty by default
 -v, --verbose                      If specified - verbose info is printed on FS console
]]

opts, args, err = require('app.custom.functions.optargs').from_opthelp(opthelp, argv)

if opts == nil then
    freeswitch.consoleLog("ERROR", "[NORMALIZE CALLERID] Options are not parsable " .. err)
    do return end
end

if session:ready() then

    local ani = session:getVariable("ani")
    ani = ani:gsub("%D", "")
   
    local append_ani = ""
    if opts.a == nil or opts.a == "yes" or opts.a == "true" then
        append_ani = ani
    end

    fsLog("ANI to append: " .. append_ani)

    local prepend_prefix = opts.p and opts.p or ""

    fsLog("Prefix to prepend: " .. prepend_prefix)

    local trunk = opts.t and string.lower(opts.t) or 'astpp4'

    fsLog("Trunk is being used: " .. trunk)

    if trunk ~= 'telgo' and trunk ~= 'astpp4' then
        freeswitch.consoleLog("ERROR", "[NORMALIZE CALLERID] Trunk " .. trunk .. " is not supported!")
        do return end
    end

    -- Check if call is forwarded
    if (ani:len() > 5) then -- Seems, we have forwaded call
        fsLog("Call is forwarded. Processing callerid " .. ani)

        if trunk == 'telgo' then
            local caller_id =  ani
            setCallerID(caller_id, caller_id)
            do return end
        
        elseif trunk == 'astpp4' then

            local local_caller_id = getFusionCompanyCallerid(prepend_prefix, "")

            if local_caller_id then
                -- ON ASTPP there is a special rule for Forwarded Calls. Append Diversion Header there
                local caller_id_name = "F" .. ani
                local caller_id_number = local_caller_id

                fsLog("Setting CallerID to " .. caller_id_name .. " <" .. caller_id_number .. ">")
                setCallerID(caller_id_name, caller_id_number)
                do return end
            end

            local caller_id = prepend_prefix .. ani

            fsLog("Cannot set local callerID. Setting as is to  " .. caller_id)
            setCallerID(caller_id, caller_id)

            do return end
        end
    end

    outbound_caller_id_name = session:getVariable("outbound_caller_id_name") or ""

    if string.find(outbound_caller_id_name:lower(), "anon") then
        fsLog("Call is anonymous")
        if trunk == 'telgo' then
             -- No matter what to do, just set callerid to Anon
            setCallerID("anonymous", "anonymous")
            session:setVariable("sip_cid_type","rpid")
            session:setVariable("origination_privacy", "screen+hide_name+hide_number")
            do return end
        end

        if trunk == 'astpp4' then
            local local_caller_id = getFusionCompanyCallerid(prepend_prefix, append_ani)

            if local_caller_id then
                setCallerID("anonymous", local_caller_id)
                do return end
            end
            fsLog("Cannot set local callerID. Doing nothing")
            do return end
        end
    end

    -- Check for outbound callerid number... Faking calls actually
    
    local presented_caller_id_number = session:getVariable("outbound_caller_id_number") or ""
    presented_caller_id_number = presented_caller_id_number:gsub("%D", "")

    local local_caller_id = getFusionCompanyCallerid(prepend_prefix, append_ani)

    if #presented_caller_id_number > 0 then

        presented_caller_id_number = prepend_prefix .. presented_caller_id_number

        if trunk == 'telgo' then
            fsLog("Callerid is set based on user settings " .. presented_caller_id_number)

            setCallerID(presented_caller_id_number, presented_caller_id_number)
            do return end
        end
        
        if trunk == 'astpp4' then
            -- Here we swapping callerID name and number as on ASTPP4 we're assuming real number is in number, fake is in name
            local presented_caller_id_name = presented_caller_id_number
            presented_caller_id_number = local_caller_id or presented_caller_id_number
            fsLog("Faking CallerID as F:" .. presented_caller_id_name .. " R:<" .. presented_caller_id_number .. ">")
            setCallerID(presented_caller_id_name, presented_caller_id_number)
            do return end
        end
    end

    -- Normal outbound call

    if (local_caller_id) then
        fsLog("Callerid is set based on company_caller_id: " .. local_caller_id)
        setCallerID(local_caller_id, local_caller_id)
        do return end
    end

    fsLog("No action were preformed")

end