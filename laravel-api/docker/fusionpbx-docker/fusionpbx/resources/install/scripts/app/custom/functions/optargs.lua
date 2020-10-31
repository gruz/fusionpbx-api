--[[
Simple command line option parser
Copyright (c) 2015 Natanael Copa <ncopa@alpinelinux.org>

License: MIT
http://opensource.org/licenses/MIT

--]]

local M = {}

local function validate_opt(i, argv, valid, opt, target)
	if not valid then
		return nil, "invalid option"
	end
    if valid.has_arg then
		local optarg = argv[i]
		if opt:match("=") then
			optarg = opt:gsub("[^=]+=(.*)", "%1")
		end
		if optarg == nil then
			return nil, "optarg required"
		end
		for _,s in pairs{'shortopt', 'longopt'} do
            if valid[s] then
                -- Create table for multiple parameter specify
                if (valid.optarg == 'C') then
                    if (target[valid[s]] ~= nil) then
                        table.insert(target[valid[s]], optarg)
                    else
                        target[valid[s]] = {optarg}
                    end 
                else
                    target[valid[s]] = optarg
                end
			end
		end
		i = i + 1
	else
		for _,s in pairs{'shortopt', 'longopt'} do
			if valid[s] then
				target[valid[s]] = (target[valid[s]] or 0) + 1
			end
		end
	end
	return i
end

function M.from_opthelp(opthelp, raw_args, errfunc)
	local valid_shortopts = {}
	local valid_longopts = {}
	local opts = {}
	local args = {}
	local moreopts = true
	raw_args = raw_args or argv
	errfunc = errfunc or function(opt, errstr)
		io.stderr:write(("%s: %s\n"):format(opt, errstr))
		return nil, opt, errstr
	end
--	for line in opthelp:gmatch("[^\n]+") do
--		local short, long, has_arg = parse_helpline(line)
--		print("line: ", line)
--	end

	-- search for: -a, --longopt[=OPTARG]
	for shortopt, longopt, separator, optarg in opthelp:gmatch("%s+%-(%w),%s?%-%-([%w-_]+)([%s=])([A-Z]?)") do
		valid_shortopts[shortopt] = {
			has_arg = (separator == "="),
			shortopt = shortopt,
            longopt = longopt,
            optarg = optarg
		}
		valid_longopts[longopt] = valid_shortopts[shortopt]
    end

	-- search for: --longopt[=OPTARG]
	for longopt, separator, optarg in opthelp:gmatch("[^,]%s+%-%-([%w-_]+)([%s=])([A-Z]?)") do
		if not valid_longopts[longopt] then
			valid_longopts[longopt] = {
				has_arg = (separator == "="),
                longopt = longopt,
                optarg = optarg
			}
		end
	end

	-- search for: -a [OPTARG]
	for shortopt, separator, optarg in opthelp:gmatch("%s+%-(%w)(%s)([A-Z%s]?)") do
		local has_arg = (separator == " " and not string.match(optarg or "", "%s"))
		valid_shortopts[shortopt] = {
			has_arg = has_arg,
            shortopt = shortopt,
            optarg = optarg
		}
    end

	local i = 1
	while i <= #raw_args do
		local err
		local a = raw_args[i]
		i = i + 1
		if a == "--" then
			moreopts = false
		elseif moreopts and a:sub(1,2) == "--" then
			local opt = a:sub(3)
			i, err = validate_opt(i, raw_args, valid_longopts[opt:gsub("=.*", "")], opt, opts)
			if not i then
				return errfunc(a, err)
			end
		elseif moreopts and #a > 1 and a:sub(1,1) == "-" then
			for j = 2, #a do
				local opt = a:sub(j,j)
				i, err = validate_opt(i, raw_args, valid_shortopts[opt], opt, opts)
				if not i then
					return errfunc(a, err)
				end
			end
		else
			args[#args + 1] = a
		end
	end
	return opts, args
end


return M