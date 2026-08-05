<div x-data="accountSettingModule()" x-init="init()" x-show="tab === 'account_setting'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="card-hd">
            <div>
                <div class="display card-hd-title">Pengaturan Akun Default</div>
                <div style="font-size:12.5px; color:var(--ink-3); margin-top:2px;">
                    Akun yang digunakan sistem secara otomatis saat membentuk jurnal untuk setiap jenis transaksi.
                </div>
            </div>
            <button class="btn btn-primary btn-sm" x-on:click="submitSettings()" :disabled="saving">
                <x-misc.icon name="check" :size="13" />
                <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
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
                                <select class="input" style="max-width:360px;" x-model="settings[item.key]">
                                    <option value="">— Tidak diatur —</option>
                                    <template x-for="a in allAccounts" :key="a.id">
                                        <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                                    </template>
                                </select>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </template>
        </table>
    </div>
</div>
