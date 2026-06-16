@php($workLog = $gareyManWorkLog)
<x-app-layout>
    <x-slot name="header">Edit Garey Man Work Log</x-slot>

    <div class="min-h-[calc(100vh-11rem)] flex items-center justify-center py-8">
        <div class="w-full max-w-4xl bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <form method="POST" action="{{ route('garey-man-work-logs.update', $gareyManWorkLog) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('garey_man_work_logs._form')
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('garey-man-work-logs.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
