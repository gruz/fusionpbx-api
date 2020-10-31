-- modify MOH from local_stream to file_stream
require "app.custom.moh_fix.resources.functions.local_to_file_stream"

if (session:ready()) then
    hold_music = session:getVariable("hold_music")
    --freeswitch.consoleLog("notice", "[FIX_MOH] Got moh "..hold_music.."\n")
    if (hold_music ~= nil) then
        hold_music = local_to_file_stream(hold_music)
        session:execute("export", "hold_music=" .. hold_music)
    end

    transfer_ringback = session:getVariable("transfer_ringback")
    if (transfer_ringback ~= nil) then
        transfer_ringback = local_to_file_stream(transfer_ringback)
        session:execute("export", "transfer_ringback=" .. transfer_ringback)
    end

    ringback = session:getVariable("ringback")
    if (ringback ~= nil) then
        ringback = local_to_file_stream(ringback)
        session:execute("export", "ringback=" .. ringback)
    end
    --freeswitch.consoleLog("notice", "[FIX_MOH] Set moh "..hold_music.."\n")
    session:setVariable("fix_moh", "true")
end
