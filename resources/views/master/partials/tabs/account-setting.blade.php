<div x-data="accountSettingModule()" x-init="init()" x-show="tab === 'account_setting'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="card-hd">
            <div>
                <div class="display card-hd-title">Pengaturan Akun Default</div>
                <div style="font-size:12.5px; color:var(--ink-3); margin-top:2px;">
                    Akun yang digunakan sistem secara otomatis saat membentuk jurnal untuk setiap jenis transaksi.
                </div>
            </div>
            <button class="btn btn-primary btn-sm" x-on:click="submitSettings()">
                <x-misc.icon name="check" :size="13" />
                <span x-text="'Simpan Perubahan'"></span>
            </button>
        </div>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Pengaturan</th>
                    <th>Akun</th>
                </tr>
            </thead>
            <template x-for="group in groups" :key="group.group">
                <tbody>
                    <tr class="coa-group-row">
                        <td colspan="2" x-text="group.group"></td>
                    </tr>
                    <template x-for="item in group.items" :key="item.key">
                        <tr>
                            <td style="font-weight:600; font-size:13px;" x-text="item.label"></td>
                            <td>
                                <x-misc.select display="settings[item.key] ? (allAccounts.find(a => a.id === settings[item.key])?.code + ' - ' + allAccounts.find(a => a.id === settings[item.key])?.name) : 'Pilih akun'"
                                    hasValue="settings[item.key]" placeholder="Cari akun...">
                                    <template x-for="a in allAccounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()))"
                                        :key="a.id">
                                        <div class="dropdown-item"
                                            @click="settings[item.key]=a.id; open=false; q=''">
                                            <div style="font-size:13px;" x-text="a.name"></div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.code"></div>
                                        </div>
                                    </template>
                                    <template
                                        x-if="!allAccounts.some(a => !q || a.name.toLowerCase().includes(q.toLowerCase()))">
                                        <div class="dropdown-empty">Tidak ditemukan</div>
                                    </template>
                                </x-misc.select>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </template>
        </table>
    </div>
</div>
