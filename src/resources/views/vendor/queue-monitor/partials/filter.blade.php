<div class="px-6 py-4 mb-6 pl-4 rounded-md border border-gray-200 dark:border-gray-600">

    <form action="" method="get">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-2">

            <div>
                <label for="filter_name" class="block mb-1 text-xs font-light text-gray-500">
                    Job Name
                </label>

                <input type="text" id="filter_name" name="name" value="{{ $filters['name'] ?? null }}"
                    placeholder="Example: ProcessPodcast" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            @if (config('queue-monitor.ui.show_custom_data'))
                <div>
                    <label for="filter_custom_data" class="block mb-1 text-xs font-light text-gray-500">
                        Custom Data
                    </label>

                    <input type="text" id="filter_custom_data" name="custom_data"
                        value="{{ $filters['custom_data'] ?? null }}" placeholder="Search custom data..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            @endif

            <div>
                <label for="filter_status" class="block mb-1 text-xs font-light text-gray-500">
                    Status
                </label>

                <select name="status" id="filter_status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm appearance-none">

                    <option @if ($filters['status'] === null) selected @endif value="">
                        All Statuses
                    </option>

                    @foreach ($statuses as $status => $statusName)
                        <option @if ($filters['status'] === $status) selected @endif value="{{ $status }}">
                            {{ $statusName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter_queues" class="block mb-1 text-xs font-light text-gray-500">
                    Queues
                </label>

                <select name="queue" id="filter_queues"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm appearance-none">

                    <option value="all">
                        All Queues
                    </option>

                    @foreach ($queues as $queue)
                        <option @if ($filters['queue'] === $queue) selected @endif value="{{ $queue }}">
                            {{ e($queue) }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">

            <a href="{{ route('queue-monitor::index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium transition-all duration-200">
                Reset Filters
            </a>

            <button type="submit"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-all duration-200 shadow-lg shadow-indigo-600/20">
                Apply Filters
            </button>

        </div>

    </form>

</div>
