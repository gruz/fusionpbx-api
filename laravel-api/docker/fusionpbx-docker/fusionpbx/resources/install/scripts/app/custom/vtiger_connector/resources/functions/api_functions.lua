-- data is provided by this funstions
-- timestamp

function vtiger_api_call(method, credentials, data, is_return)

    local api_data = data

    api_data['timestamp'] = os.time()
    api_data['uuid'] = session:getVariable('call_uuid') or ""

    local api_string = credentials['url'] .. "call_"..method..".php content-type application/json post '"..json_encode(api_data).."'"
    if (api_data['debug']) then
        freeswitch.consoleLog("NOTICE", "[vtiger_connector][call_"..method.."] "..api_string)
    else
        local api_response = api:execute("curl ", api_string)
        freeswitch.consoleLog("NOTICE", "[vtiger_connector][call_"..method.."] Response: "..api_response)
    end

end

function ringing_answered_call(type)
    
    local vtiger_url = session:getVariable("vtiger_url")
    local vtiger_api_key = session:getVariable("vtiger_api_key")
	if (vtiger_url == nil or vtiger_api_key == nil) then
		freeswitch.consoleLog("WARNING", "[vtiger_connector]["..type.."] Can't get URL or key")
		do return end
    end
    
    local credentials = {}
	credentials['url'], credentials['key'] = dec64(vtiger_url), dec64(vtiger_api_key)
	local dialed_user = session:getVariable("dialed_user")
	if (dialed_user == nil) then
		freeswitch.consoleLog("WARNING", "[vtiger_connector]["..type.."] Can't get dialed user")
		do return end
	end
	local call_data = {}
    call_data['number'] = dialed_user
    --call_data['debug'] = true

	vtiger_api_call(type, credentials, call_data)
end

-- Prepare JSON strings
function json_encode(data)
    local function string(o)
        return '"' .. tostring(o) .. '"'
    end
    local function recurse(o, indent)
        if indent == nil then
            indent = ''
        end
        indent = indent.."{"
        for k,v in pairs(o) do
            indent = indent .. string(k) .. ":"
            if type(v) == 'table' then
                indent = indent .. recurse(v)
            else 
                indent = indent .. string(v) .. ","
            end
        end
        return indent:sub(0, -2) .. "},"
    end
    if type(data) ~= 'table' then
        return nil
    end
    return recurse(data):sub(0, -2)
end

-- Get call direction
function get_call_direction(src, dst)

    -- Emergency routes
    local emergency_table = {}
    emergency_table['911'] = 1

    local src_len = src:len()
    local dst_len = dst:len()

    if emergency_table[dst] ~= nil and dst_len >= 7 then
        return "outbound"
    end

    if (src_len > 7) then
        return "inbound"
    end

    return "local"
end


-- Base64 encoding/decoding
-- encoding
function enc64(data)
    local b='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'

    return ((data:gsub('.', function(x) 
        local r,b='',x:byte()
        for i=8,1,-1 do r=r..(b%2^i-b%2^(i-1)>0 and '1' or '0') end
        return r;
    end)..'0000'):gsub('%d%d%d?%d?%d?%d?', function(x)
        if (#x < 6) then return '' end
        local c=0
        for i=1,6 do c=c+(x:sub(i,i)=='1' and 2^(6-i) or 0) end
        return b:sub(c+1,c+1)
    end)..({ '', '==', '=' })[#data%3+1])
end

-- decoding
function dec64(data)

    local b='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
    data = string.gsub(data, '[^'..b..'=]', '')
    return (data:gsub('.', function(x)
        if (x == '=') then return '' end
        local r,f='',(b:find(x)-1)
        for i=6,1,-1 do r=r..(f%2^i-f%2^(i-1)>0 and '1' or '0') end
        return r;
    end):gsub('%d%d%d?%d?%d?%d?%d?%d?', function(x)
        if (#x ~= 8) then return '' end
        local c=0
        for i=1,8 do c=c+(x:sub(i,i)=='1' and 2^(8-i) or 0) end
        return string.char(c)
    end))
end
