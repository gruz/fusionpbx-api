-- App for simple testing DTMF

local log = require "resources.functions.log".dtmf_test

if (session:ready()) then
    log.notice("Starting DTMF Test")
    session:answer()
    loops_count = session:getVariable("dtmf_test_loops_count") or 10
    max_digits = session:getVariable("dtmf_test_max_digits") or 3
end


for i = 1, loops_count, 1 do
    if (session:ready()) then
        log.notice("Looping test # " .. i .. ". Getting digits")
        digits = session:playAndGetDigits(1, max_digits, 1, 5000, "#", "ivr/ivr-enter_destination_telephone_number.wav", "", "\\d+")
        log.notice("Looping test # " .. i .. ". Got digits: " .. digits)
        session:say(digits, "en", "name_spelled", "iterated")
        session:sleep(100)
    else
        break
    end
end

log.notice("Ending DTMF Test")
session:hangup()