{{--
    Partial: pilih file dari upload baru ATAU dari Member Area (kalau sedang login).
    Wajib set variabel sebelum @include:
    - $inputName        : nama input file untuk upload baru (misal 'photo' atau 'photos[]')
    - $memberInputName  : nama input untuk ID file member (misal 'member_file_id' atau 'member_file_ids[]')
    - $multiple         : bool, apakah boleh pilih banyak file
    - $accept           : atribut accept HTML untuk input upload
    - $eligibleFiles     : koleksi MemberFile yang eligible (kosong kalau guest)
--}}
@if (auth()->check())
    <div class="flex gap-2 mb-3 text-xs font-semibold" x-data="{}">
        <button type="button" onclick="showUploadTab(this)"
                class="tab-upload px-3 py-1.5 rounded-lg bg-slate-900 text-white">📤 Upload Baru</button>
        <button type="button" onclick="showMemberTab(this)"
                class="tab-member px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600">📁 Pilih dari File Manager</button>
    </div>
@endif

<div class="source-upload">
    {{ $slot }}
</div>

@if (auth()->check())
    <div class="source-member hidden mt-2">
        @if ($eligibleFiles->isEmpty())
            <p class="text-sm text-slate-400 py-4 text-center border border-dashed border-slate-200 rounded-xl">
                Belum ada file yang cocok di File Manager kamu.
            </p>
        @else
            <div class="max-h-56 overflow-y-auto border border-slate-200 rounded-xl divide-y">
                @foreach ($eligibleFiles as $mf)
                    <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm">
                        <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="{{ $memberInputName }}" value="{{ $mf->id }}"
                               class="rounded border-slate-300">
                        <span class="truncate flex-1">{{ $mf->original_name }}</span>
                        <span class="text-xs text-slate-400 shrink-0">{{ $mf->human_size }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
@endif

@once
    <script>
        function showUploadTab(btn) {
            const form = btn.closest('form');
            form.querySelector('.source-upload').classList.remove('hidden');
            form.querySelector('.source-member')?.classList.add('hidden');
            form.querySelector('.tab-upload').className = 'tab-upload px-3 py-1.5 rounded-lg bg-slate-900 text-white';
            form.querySelector('.tab-member').className = 'tab-member px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600';
        }
        function showMemberTab(btn) {
            const form = btn.closest('form');
            form.querySelector('.source-upload').classList.add('hidden');
            form.querySelector('.source-member')?.classList.remove('hidden');
            form.querySelector('.tab-member').className = 'tab-member px-3 py-1.5 rounded-lg bg-slate-900 text-white';
            form.querySelector('.tab-upload').className = 'tab-upload px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600';
        }
    </script>
@endonce
