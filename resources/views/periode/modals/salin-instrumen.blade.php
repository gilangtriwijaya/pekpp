{{-- Modal: Salin Instrumen Antar Periode --}}
<div class="periode-modal-overlay" id="periodeSalinModal" x-data="salinInstrumenData()">
    <div class="periode-modal" style="max-width: 640px;">
        <div class="periode-modal-header">
            <h3 class="periode-modal-title">Salin Instrumen ke: <span id="salin-tujuan-nama" style="color: #2563eb;"></span></h3>
            <button class="periode-modal-close" onclick="closeSalinModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- FORM STATE --}}
        <div x-show="!showSummary">
            <div class="periode-modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto;">
                <input type="hidden" id="salin-tujuan-id">

                {{-- Dropdown Periode Sumber --}}
                <div class="periode-form-group">
                    <label class="periode-form-label" for="sumber_periode_id">Salin dari Periode</label>
                    <select id="sumber_periode_id" class="periode-form-input" x-model="sumberId" @change="loadTree()">
                        <option value="">-- Pilih Periode Sumber --</option>
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}" data-exclude="{{ $p->id }}">{{ $p->nama }} ({{ $p->tahun }})</option>
                        @endforeach
                    </select>
                    <small style="color: #6B7280; font-size: 12px; margin-top: 4px; display: block;">
                        Periode tujuan tidak ditampilkan dalam pilihan.
                    </small>
                </div>

                {{-- Loading state --}}
                <div x-show="isLoading" style="text-align: center; padding: 24px; color: #64748b;">
                    <svg style="animation: spin 1s linear infinite; margin: 0 auto 10px; display: block;" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                    </svg>
                    <style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
                    Memuat instrumen...
                </div>

                {{-- Empty state --}}
                <div x-show="!isLoading && sumberId && tree.length === 0 && f03Tree.length === 0" style="padding: 16px; background: #fef2f2; color: #ef4444; border-radius: 8px; text-align: center; font-size: 14px;">
                    Tidak ada instrumen di periode sumber ini.
                </div>

                {{-- Tree Instrumen F01 --}}
                <div x-show="!isLoading && tree.length > 0" class="periode-form-group">
                    <label class="periode-form-label">Pilih Instrumen F01 yang Akan Disalin</label>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-y: auto; max-height: 20rem; padding: 12px; background: #f8fafc;">
                        <template x-for="aspek in tree" :key="aspek.id">
                            <div style="margin-bottom: 10px;">
                                {{-- Aspek --}}
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer; padding: 4px 0;">
                                    <input type="checkbox"
                                           style="width: 15px; height: 15px; cursor: pointer;"
                                           :checked="isAspekChecked(aspek)"
                                           :indeterminate.prop="isAspekIndeterminate(aspek)"
                                           @change="toggleAspek(aspek, $event.target.checked)">
                                    <span x-text="aspek.kode + ' · ' + aspek.nama"></span>
                                </label>

                                {{-- Indikators --}}
                                <div style="margin-left: 20px; border-left: 2px solid #cbd5e1; padding-left: 12px; margin-top: 2px;">
                                    <template x-for="indikator in aspek.indikator" :key="indikator.id">
                                        <div style="margin-bottom: 6px;">
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #334155; font-weight: 500; padding: 2px 0;">
                                                <input type="checkbox"
                                                       style="width: 14px; height: 14px; cursor: pointer;"
                                                       :checked="isIndikatorChecked(indikator)"
                                                       :indeterminate.prop="isIndikatorIndeterminate(indikator)"
                                                       @change="toggleIndikator(aspek, indikator, $event.target.checked)">
                                                <span x-text="indikator.kode + ' · ' + indikator.nama" style="font-size: 13px;"></span>
                                            </label>

                                            {{-- Pertanyaan --}}
                                            <div style="margin-left: 20px; border-left: 1px dashed #cbd5e1; padding-left: 10px; margin-top: 2px;">
                                                {{-- Non-conditional first --}}
                                                <template x-for="p in getNonConditional(indikator.pertanyaan)" :key="p.id">
                                                    <div>
                                                        <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; padding: 2px 0;">
                                                            <input type="checkbox"
                                                                   style="width: 13px; height: 13px; cursor: pointer; margin-top: 2px; flex-shrink: 0;"
                                                                   :value="p.id"
                                                                   x-model="selectedPertanyaans"
                                                                   @change="onPertanyaanChange(aspek, indikator)">
                                                            <span x-text="p.label" style="font-size: 12px; color: #475569; line-height: 1.4;"></span>
                                                        </label>
                                                        {{-- Conditional children --}}
                                                        <template x-for="child in getConditional(indikator.pertanyaan, p.id)" :key="child.id">
                                                            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin-left: 20px; border-left: 1px dotted #f59e0b; padding-left: 8px; padding-top: 2px; padding-bottom: 2px;">
                                                                <input type="checkbox"
                                                                       style="width: 13px; height: 13px; cursor: pointer; margin-top: 2px; flex-shrink: 0;"
                                                                       :value="child.id"
                                                                       x-model="selectedPertanyaans"
                                                                       @change="onChildPertanyaanChange(aspek, indikator, p.id)">
                                                                <span style="font-size: 12px; color: #92400e; line-height: 1.4;">
                                                                    <span style="background: #fef3c7; color: #b45309; border-radius: 3px; padding: 0 4px; font-size: 11px; font-weight: 600;">Kondisional</span>
                                                                    <span x-text="' ' + child.label"></span>
                                                                </span>
                                                            </label>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tree Instrumen F03 --}}
                <div x-show="!isLoading && f03Tree.length > 0" class="periode-form-group" style="margin-top: 20px;">
                    <label class="periode-form-label">Pilih Instrumen F03 yang Akan Disalin</label>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-y: auto; max-height: 20rem; padding: 12px; background: #f8fafc;">
                        <template x-for="aspek in f03Tree" :key="aspek.id">
                            <div style="margin-bottom: 10px;">
                                {{-- Aspek --}}
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer; padding: 4px 0;">
                                    <input type="checkbox"
                                           style="width: 15px; height: 15px; cursor: pointer;"
                                           :checked="isF03AspekChecked(aspek)"
                                           :indeterminate.prop="isF03AspekIndeterminate(aspek)"
                                           @change="toggleF03Aspek(aspek, $event.target.checked)">
                                    <span x-text="aspek.kode + ' · ' + aspek.nama"></span>
                                </label>

                                {{-- Indikators --}}
                                <div style="margin-left: 20px; border-left: 2px solid #cbd5e1; padding-left: 12px; margin-top: 2px;">
                                    <template x-for="indikator in aspek.indikator" :key="indikator.id">
                                        <div style="margin-bottom: 6px;">
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #334155; font-weight: 500; padding: 2px 0;">
                                                <input type="checkbox"
                                                       style="width: 14px; height: 14px; cursor: pointer;"
                                                       :value="indikator.id"
                                                       x-model="selectedF03Indikators"
                                                       @change="onF03IndikatorChange(aspek)">
                                                <span x-text="indikator.kode + ' · ' + (indikator.pertanyaan || '')" style="font-size: 13px;"></span>
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                {{-- Opsi Tambahan --}}
                <div x-show="!isLoading && (tree.length > 0 || f03Tree.length > 0)" class="periode-form-group" style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                    <label class="periode-form-label" style="margin-bottom: 8px;">Opsi Tambahan:</label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; color: #374151;">
                        <input type="checkbox" x-model="copyF02Skor">
                        Salin Pengelolaan Skor F02 (Narasi skor untuk indikator yang disalin)
                    </label>
                </div>
            </div>

            {{-- FIXED footer: Mode + Actions --}}
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <div class="periode-form-group" style="margin-bottom: 16px;">
                    <label class="periode-form-label" style="margin-bottom: 8px;">Jika kode sudah ada di periode tujuan:</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; color: #374151;">
                            <input type="radio" name="salin_mode_radio" value="skip" x-model="mode">
                            Skip (pertahankan yang ada)
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; color: #374151;">
                            <input type="radio" name="salin_mode_radio" value="overwrite" x-model="mode">
                            Overwrite (timpa dengan sumber)
                        </label>
                    </div>
                </div>
                <div class="periode-modal-footer" style="padding: 0; border: none; background: transparent;">
                    <button type="button" class="periode-btn periode-btn-secondary" onclick="closeSalinModal()">Batal</button>
                    <button type="button" class="periode-btn periode-btn-primary"
                            @click="submitSalin()"
                            :disabled="isSubmitting || (selectedPertanyaans.length === 0 && selectedF03Indikators.length === 0)"
                            :style="(selectedPertanyaans.length === 0 && selectedF03Indikators.length === 0) ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                        <span x-show="!isSubmitting">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; vertical-align: middle; margin-right: 4px;">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            Salin yang Dipilih
                        </span>
                        <span x-show="isSubmitting">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- SUMMARY STATE --}}
        <div x-show="showSummary">
            <div class="periode-modal-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <h4 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Salin Instrumen Berhasil!</h4>
                    <p style="font-size: 14px; color: #64748b;">Berikut ringkasan proses penyalinan:</p>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 8px 0; color: #64748b;">Aspek F01</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.aspek_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.aspek_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 8px 0; color: #64748b;">Indikator F01</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.indikator_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.indikator_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 8px 0; color: #64748b;">Pertanyaan F01</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.pertanyaan_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.pertanyaan_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;" x-show="summary.f03_aspek_disalin > 0 || summary.f03_aspek_dilewati > 0">
                            <td style="padding: 8px 0; color: #64748b;">Aspek F03</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.f03_aspek_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.f03_aspek_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                        <tr x-show="summary.f03_indikator_disalin > 0 || summary.f03_indikator_dilewati > 0">
                            <td style="padding: 8px 0; color: #64748b;">Indikator F03</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.f03_indikator_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.f03_indikator_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;" x-show="summary.f02_skor_disalin > 0 || summary.f02_skor_dilewati > 0">
                            <td style="padding: 8px 0; color: #64748b;">Skor F02</td>
                            <td style="padding: 8px 0; text-align: right;">
                                <span x-text="summary.f02_skor_disalin" style="color: #16a34a; font-weight: 700;"></span>
                                <span style="color: #64748b;"> disalin · </span>
                                <span x-text="summary.f02_skor_dilewati" style="color: #f59e0b; font-weight: 700;"></span>
                                <span style="color: #64748b;"> dilewati</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Warnings --}}
                <template x-if="summary.warning && summary.warning.length > 0">
                    <div style="margin-top: 16px; padding: 12px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                        <p style="font-size: 13px; font-weight: 600; color: #b45309; margin-bottom: 8px;">⚠ Peringatan:</p>
                        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #78350f;">
                            <template x-for="warn in summary.warning" :key="warn">
                                <li x-text="warn" style="margin-bottom: 4px;"></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
            <div class="periode-modal-footer">
                <button type="button" class="periode-btn periode-btn-primary" onclick="closeSalinModal(true)">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== TRIGGER FUNCTION =====
    function openSalinModal(periodeTarget) {
        // Set tujuan info
        document.getElementById('salin-tujuan-id').value = periodeTarget.id;
        document.getElementById('salin-tujuan-nama').textContent = periodeTarget.nama + ' (' + periodeTarget.tahun + ')';

        // Hide target periode from sumber dropdown
        const select = document.getElementById('sumber_periode_id');
        for (let i = 0; i < select.options.length; i++) {
            select.options[i].hidden = (select.options[i].value == periodeTarget.id);
        }

        // Reset alpine state via event
        document.dispatchEvent(new CustomEvent('reset-salin-modal'));

        // Open using existing openModal function
        openModal('periodeSalinModal');
    }

    function closeSalinModal(reload) {
        closeModal('periodeSalinModal');
        if (reload) {
            window.location.reload();
        }
    }

    // ===== URL TEMPLATES (generated by Blade, no subfolder hardcode) =====
    const SALIN_TREE_URL = '{{ route("admin.periode.instrumen-tree", ["periode" => "__PERIOD_ID__"]) }}';
    const SALIN_POST_URL = '{{ route("admin.periode.salin-instrumen", ["periode" => "__PERIOD_ID__"]) }}';

    // ===== ALPINE.JS COMPONENT =====
    function salinInstrumenData() {
        return {
            sumberId: '',
            isLoading: false,
            isSubmitting: false,
            tree: [],
            f03Tree: [],
            selectedPertanyaans: [],
            selectedIndikators: [],
            selectedAspeks: [],
            selectedF03Indikators: [],
            selectedF03Aspeks: [],
            mode: 'skip',
            copyF02Skor: true,
            showSummary: false,
            summary: {},

            init() {
                document.addEventListener('reset-salin-modal', () => {
                    this.sumberId = '';
                    this.tree = [];
                    this.f03Tree = [];
                    this.selectedPertanyaans = [];
                    this.selectedIndikators = [];
                    this.selectedAspeks = [];
                    this.selectedF03Indikators = [];
                    this.selectedF03Aspeks = [];
                    this.mode = 'skip';
                    this.copyF02Skor = true;
                    this.showSummary = false;
                    this.summary = {};
                    // Also reset the select element value
                    const sel = document.getElementById('sumber_periode_id');
                    if (sel) sel.value = '';
                });
            },

            async loadTree() {
                if (!this.sumberId) {
                    this.tree = [];
                    this.f03Tree = [];
                    return;
                }
                const targetId = document.getElementById('salin-tujuan-id').value;
                this.isLoading = true;
                this.tree = [];
                this.f03Tree = [];
                this.selectedPertanyaans = [];
                this.selectedIndikators = [];
                this.selectedAspeks = [];
                this.selectedF03Indikators = [];
                this.selectedF03Aspeks = [];
                try {
                    const treeUrl = SALIN_TREE_URL.replace('__PERIOD_ID__', targetId) + '?sumber=' + this.sumberId;
                    const response = await fetch(
                        treeUrl,
                        {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }
                    );
                    if (response.ok) {
                        const data = await response.json();
                        this.tree = data.f01 || [];
                        this.f03Tree = data.f03 || [];
                    } else {
                        const err = await response.json();
                        alert(err.error || 'Gagal memuat instrumen.');
                    }
                } catch (e) {
                    console.error('Load tree error:', e);
                    alert('Terjadi kesalahan saat memuat instrumen.');
                } finally {
                    this.isLoading = false;
                }
            },

            getNonConditional(pertanyaans) {
                return pertanyaans.filter(p => !p.parent_pertanyaan_id);
            },

            getConditional(pertanyaans, parentId) {
                return pertanyaans.filter(p => p.parent_pertanyaan_id == parentId);
            },

            // === CHECKBOX HELPERS ===
            isAspekChecked(aspek) {
                if (!aspek.indikator.length) return false;
                return aspek.indikator.every(i => this.isIndikatorChecked(i));
            },
            isAspekIndeterminate(aspek) {
                if (!aspek.indikator.length) return false;
                const some = aspek.indikator.some(i => this.isIndikatorChecked(i) || this.isIndikatorIndeterminate(i));
                return some && !this.isAspekChecked(aspek);
            },
            isIndikatorChecked(indikator) {
                if (!indikator.pertanyaan.length) return false;
                return indikator.pertanyaan.every(p => this.selectedPertanyaans.map(Number).includes(p.id));
            },
            isIndikatorIndeterminate(indikator) {
                if (!indikator.pertanyaan.length) return false;
                const some = indikator.pertanyaan.some(p => this.selectedPertanyaans.map(Number).includes(p.id));
                return some && !this.isIndikatorChecked(indikator);
            },

            // === TOGGLE ASPEK ===
            toggleAspek(aspek, isChecked) {
                aspek.indikator.forEach(ind => {
                    ind.pertanyaan.forEach(p => {
                        const idx = this.selectedPertanyaans.indexOf(p.id);
                        const idxStr = this.selectedPertanyaans.indexOf(String(p.id));
                        if (isChecked) {
                            if (idx === -1 && idxStr === -1) this.selectedPertanyaans.push(p.id);
                        } else {
                            this.selectedPertanyaans = this.selectedPertanyaans.filter(id => Number(id) !== p.id);
                        }
                    });
                    if (isChecked) {
                        if (!this.selectedIndikators.includes(ind.id)) this.selectedIndikators.push(ind.id);
                    } else {
                        this.selectedIndikators = this.selectedIndikators.filter(id => id !== ind.id);
                    }
                });
                if (isChecked) {
                    if (!this.selectedAspeks.includes(aspek.id)) this.selectedAspeks.push(aspek.id);
                } else {
                    this.selectedAspeks = this.selectedAspeks.filter(id => id !== aspek.id);
                }
            },

            // === TOGGLE INDIKATOR ===
            toggleIndikator(aspek, indikator, isChecked) {
                indikator.pertanyaan.forEach(p => {
                    if (isChecked) {
                        if (!this.selectedPertanyaans.map(Number).includes(p.id)) this.selectedPertanyaans.push(p.id);
                    } else {
                        this.selectedPertanyaans = this.selectedPertanyaans.filter(id => Number(id) !== p.id);
                    }
                });
                if (isChecked) {
                    if (!this.selectedIndikators.includes(indikator.id)) this.selectedIndikators.push(indikator.id);
                } else {
                    this.selectedIndikators = this.selectedIndikators.filter(id => id !== indikator.id);
                }
                this.syncAspekState(aspek);
            },

            // === ON PERTANYAAN CHANGE ===
            onPertanyaanChange(aspek, indikator) {
                this.syncIndikatorState(indikator);
                this.syncAspekState(aspek);
            },

            // === ON CONDITIONAL CHILD CHANGE ===
            onChildPertanyaanChange(aspek, indikator, parentId) {
                // If any child is checked, force parent to be checked
                const hasCheckedChild = indikator.pertanyaan
                    .filter(p => p.parent_pertanyaan_id == parentId)
                    .some(p => this.selectedPertanyaans.map(Number).includes(p.id));

                if (hasCheckedChild && !this.selectedPertanyaans.map(Number).includes(parentId)) {
                    this.selectedPertanyaans.push(parentId);
                }
                this.syncIndikatorState(indikator);
                this.syncAspekState(aspek);
            },

            syncIndikatorState(indikator) {
                const anyChecked = indikator.pertanyaan.some(p => this.selectedPertanyaans.map(Number).includes(p.id));
                if (anyChecked && !this.selectedIndikators.includes(indikator.id)) {
                    this.selectedIndikators.push(indikator.id);
                } else if (!anyChecked) {
                    this.selectedIndikators = this.selectedIndikators.filter(id => id !== indikator.id);
                }
            },

            syncAspekState(aspek) {
                const anyChecked = aspek.indikator.some(i => this.selectedIndikators.includes(i.id));
                if (anyChecked && !this.selectedAspeks.includes(aspek.id)) {
                    this.selectedAspeks.push(aspek.id);
                } else if (!anyChecked) {
                    this.selectedAspeks = this.selectedAspeks.filter(id => id !== aspek.id);
                }
            },

            // === F03 CHECKBOX HELPERS ===
            isF03AspekChecked(aspek) {
                if (!aspek.indikator.length) return false;
                return aspek.indikator.every(i => this.selectedF03Indikators.map(Number).includes(i.id));
            },
            isF03AspekIndeterminate(aspek) {
                if (!aspek.indikator.length) return false;
                const some = aspek.indikator.some(i => this.selectedF03Indikators.map(Number).includes(i.id));
                return some && !this.isF03AspekChecked(aspek);
            },
            toggleF03Aspek(aspek, isChecked) {
                aspek.indikator.forEach(ind => {
                    if (isChecked) {
                        if (!this.selectedF03Indikators.map(Number).includes(ind.id)) this.selectedF03Indikators.push(ind.id);
                    } else {
                        this.selectedF03Indikators = this.selectedF03Indikators.filter(id => Number(id) !== ind.id);
                    }
                });
                this.syncF03AspekState(aspek);
            },
            onF03IndikatorChange(aspek) {
                this.syncF03AspekState(aspek);
            },
            syncF03AspekState(aspek) {
                const anyChecked = aspek.indikator.some(i => this.selectedF03Indikators.map(Number).includes(i.id));
                if (anyChecked && !this.selectedF03Aspeks.includes(aspek.id)) {
                    this.selectedF03Aspeks.push(aspek.id);
                } else if (!anyChecked) {
                    this.selectedF03Aspeks = this.selectedF03Aspeks.filter(id => id !== aspek.id);
                }
            },

            // === SUBMIT ===
            async submitSalin() {
                if (!this.sumberId) { alert('Pilih periode sumber terlebih dahulu.'); return; }
                if (this.selectedPertanyaans.length === 0 && this.selectedF03Indikators.length === 0) { alert('Pilih minimal satu instrumen untuk disalin.'); return; }

                this.isSubmitting = true;
                const targetId = document.getElementById('salin-tujuan-id').value;

                try {
                    const postUrl = SALIN_POST_URL.replace('__PERIOD_ID__', targetId);
                const response = await fetch(postUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            sumber_periode_id: parseInt(this.sumberId),
                            mode: this.mode,
                            aspek_ids: this.selectedAspeks,
                            indikator_ids: this.selectedIndikators,
                            pertanyaan_ids: this.selectedPertanyaans.map(Number),
                            f03_aspek_ids: this.selectedF03Aspeks,
                            f03_indikator_ids: this.selectedF03Indikators.map(Number),
                            copy_f02_skor: this.copyF02Skor
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.summary = result.summary;
                        this.showSummary = true;
                    } else {
                        alert(result.error || 'Gagal menyalin instrumen. Silakan coba lagi.');
                    }
                } catch (e) {
                    console.error('Submit error:', e);
                    alert('Terjadi kesalahan pada server.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }
</script>
