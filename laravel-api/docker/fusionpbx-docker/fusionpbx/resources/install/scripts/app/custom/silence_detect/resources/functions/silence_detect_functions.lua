function silence_detect_samples(samples, algo_opts) 

	-- Differece in 2 close samples to say, that change was done
	local silence_threshold = algo_opts[1] and tonumber(algo_opts[1]) or 100

	-- How many silence_threshold to consider, that it's silence and not false-positive
	local threshold_total_hits = algo_opts[2] and algo_opts(argv[2]) or 5

	local debug_param_line = " ST: " .. silence_threshold .. " TTH: " .. threshold_total_hits

	local first_sample = samples[1]
	local hits = 0

	for i = 2, #samples do
		if (math.abs(first_sample - samples[i]) > silence_threshold) then
			if (hits >= threshold_total_hits) then
				return false, " NOISE S:" .. i .. debug_param_line
			end
			hits = hits + 1
		end
		first_sample = samples[i]
	end

	return true, "SIL" .. debug_param_line
end

function silence_detect_lines(samples, algo_opts)
	local samples_length = #samples

	-- Should be small here
	local line_peak_ratio = algo_opts[1] and tonumber(algo_opts[1]) or 70
	local silence_threshold = algo_opts[2] and tonumber(algo_opts[2]) or 20
	local silence_threshold_zero = algo_opts[3] and tonumber(algo_opts[3]) or 30
	local quantinizer = algo_opts[4] and tonumber(algo_opts[4]) or 100

	local min_line_lenght = math.floor(samples_length / quantinizer)

	local line_length = 0
	local current_line_lenght = 0

	local prev_sample = samples[1]

	for i = 2, #samples do
		local current_sample = samples[i]
		if ((math.abs(current_sample) <=  silence_threshold_zero) or (math.abs(prev_sample - current_sample) <= silence_threshold)) then
			-- Check if we are in the line. Not changing prev_sample here to avoid slow constant change
			current_line_lenght = current_line_lenght + 1
		else
			-- Line had ended
			if (current_line_lenght > min_line_lenght) then
				line_length = line_length + current_line_lenght
			end
			current_line_lenght = 0
			prev_sample = current_sample
		end
	end

	-- Line had ended anyway
	if (current_line_lenght > min_line_lenght) then
		line_length = line_length + current_line_lenght
	end

	local current_line_peak_ratio = math.floor(line_length / samples_length * 100)

	local debug_param_line = "L/P C:" .. current_line_peak_ratio .. " ST:" .. silence_threshold .. " STZ:" .. silence_threshold_zero .. " L/P E:" .. line_peak_ratio .. " Q:" .. quantinizer
	if (current_line_peak_ratio > line_peak_ratio) then
		return true, debug_param_line
	end
	return false, debug_param_line
end

function silence_detect_file(filename, algo, algo_opts)

	local file_reader = wav.create_context(filename, 'r')
	
	if (file_reader == false) then
		return false, "File " .. filename .. " not found"
	end

    file_reader.set_position(0)

    -- Read only channel 1
    local samples = file_reader.get_samples(math.floor(file_reader.get_samples_per_channel()) - 1)[1]

	file_reader.close_context()
	
	local function_name = "silence_detect_" .. algo
	
	if (samples and _G[function_name]) then
		return _G[function_name](samples, algo_opts)
	end
	return false, "No samples or no function " .. function_name .. " exist"
end