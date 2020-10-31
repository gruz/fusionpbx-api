-- This function is called like app_custom.lua vtiger_connector
-- You MUST specify VTiger URL in Default (or Domain) settings.
-- Also it uses freeswitch curl command, so it also need to be loaded

-- Vars to specify
-- url
-- api_key

local log = require "resources.functions.log".sms

require "app.custom.vtiger_connector.resources.functions.api_functions"

local app_name = argv[2]
api = freeswitch.API()

if (app_name and app_name ~= 'main') then
    loadfile(scripts_dir .. "/app/custom/vtiger_connector/" .. app_name .. ".lua")(argv)
    do return end
end

local Settings = require "resources.functions.lazy_settings"
local Database = require "resources.functions.database"

local db = Database.new('system')

assert(db:connected())

local license_key = argv[3] or '';
local execute_on_ring_suffix = argv[4] or '3';
local execute_on_answer_suffix = argv[5] or '3';

if (session:ready()) then

    local domain_name = session:getVariable('domain_name')
    local domain_uuid = session:getVariable('domain_uuid')

    local settings = Settings.new(db, domain_name, domain_uuid)

    local vtiger_settings_enabled = settings:get('vtiger', 'enabled', 'boolean')

    if vtiger_settings_enabled ~= 'true' then
        log.info("VTiger is not enabled. Exiting")

        do return end
    end

    local vtiger_settings_url = settings:get('vtiger', 'url', 'text')
    local vtiger_settings_api_key = settings:get('vtiger', 'api_password', 'text')
    local vtiger_settings_record_path = settings:get('vtiger', 'record_path', 'text')
    
    log.notice("Got Vtiger URL("..vtiger_settings_url..") and key("..vtiger_settings_api_key..")")
    session:execute("export", "vtiger_url="..enc64(vtiger_settings_url))
    session:execute("export", "vtiger_api_key="..enc64(vtiger_settings_api_key))
    session:execute("export", "vtiger_record_path="..enc64(vtiger_settings_record_path))    
    session:execute("export", "nolocal:execute_on_ring_"..execute_on_ring_suffix.."=lua app_custom.lua vtiger_connector ringing")
    session:execute("export", "nolocal:execute_on_answer_"..execute_on_answer_suffix.."=lua app_custom.lua vtiger_connector answer")
    local call_start_data = {}
    
    local src = {}
    src['name'] = session:getVariable('caller_id_name') or ""
    src['number'] = session:getVariable('caller_id_number') or ""

    local dst = session:getVariable('destination_number') or ""

    call_start_data['src'] = src
    call_start_data['dst'] = dst
    call_start_data['direction'] = get_call_direction(src['number'], dst)

    local credentials = {}
    credentials['url'], credentials['key'] = vtiger_settings_url, vtiger_settings_api_key
    vtiger_api_call("start", credentials, call_start_data)
end