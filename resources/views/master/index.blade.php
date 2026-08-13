@extends('layouts.app')
@section('content')
    <script>
        function masterPageData() {
            return {
                tab: sessionStorage.getItem('master_tab') || 'produk',
                modal: null,

                // shared data for modals
                productUnits: @json($units),
                productCategories: @json($productCategories),
                inventoryAccounts: @json($inventoryAccounts),
                salesAccounts: @json($salesAccounts),
                cogsAccounts: @json($cogsAccounts),
                receivableAccounts: @json($receivableAccounts),
                payableAccounts: @json($payableAccounts),
                accountCategoriesAll: @json($accountCategories),
                contactOptions: @json($contactOptions),
                userRoles: @json($userRoles),
                rolesAll: @json($roles),
            };
        }

        function productModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],
                form: {
                    id: null,
                    code: null,
                    name: null,
                    description: null,
                    category_id: null,
                    unit_id: null,
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.products.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                handleSearch(q) {
                    this.search = q;
                    this.page = 1;
                    this.fetchData();
                },
                async handleStatus(id) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    try {
                        const r = await axios.post(route('master.products.status', id));
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                        await this.fetchData();
                    } catch (e) {
                        Swal.close();
                        Toast.fire({
                            icon: 'error',
                            title: e.response?.data?.message || 'Gagal mengubah status.'
                        });
                    }
                },

                openCreateModal() {
                    this.form = {
                        id: null,
                        code: null,
                        name: null,
                        description: null,
                        category_id: null,
                        unit_id: null,
                    };
                    this.modal = 'add_product';
                },

                openEditModal(product) {
                    this.form = {
                        id: product.id,
                        code: product.code,
                        name: product.name,
                        description: product.description,
                        category_id: product.category_id,
                        unit_id: product.unit_id,
                    };
                    this.modal = 'edit_product';
                },

                async handleCreate() {
                    Swal.fire({
                        title: 'Konfirmasi Pembuatan Produk',
                        text: 'Apakah anda yakin ingin membuat produk baru dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat produk',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses penyimpanan Produk...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.post(
                                    route('master.products.store'), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Produk',
                        text: 'Apakah anda yakin ingin memperbarui produk dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui produk',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses perubahan Produk...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.products.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                }
            };

        }

        function contactModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],

                form: {
                    id: null,
                    code: '',
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    city: '',
                    state: '',
                    postal_code: '',
                    note: '',
                    is_customer: true,
                    is_supplier: false,
                    is_employee: false,
                    transportation_cost: 0,
                },

                avatarMeta(name) {
                    const words = (name || '').trim().split(/\s+/).filter(Boolean);
                    const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');
                    let h = 0;
                    for (const c of (name || '')) h = ((h * 31) + c.charCodeAt(0)) & 0xFFFFFFFF;
                    const hue = ((h % 360) + 360) % 360;
                    return {
                        initials,
                        bg: 'oklch(0.92 0.04 ' + hue + ')',
                        fg: 'oklch(0.45 0.10 ' + hue + ')'
                    };
                },

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.contacts.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                handleSearch(q) {
                    this.search = q;
                    this.page = 1;
                    this.fetchData();
                },
                async handleStatus(id) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    try {
                        const r = await axios.post(route('master.contacts.status', id));
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                        await this.fetchData();
                    } catch (e) {
                        Swal.close();
                        Toast.fire({
                            icon: 'error',
                            title: e.response?.data?.message || 'Gagal mengubah status.'
                        });
                    }
                },

                openCreateModal() {
                    this.form = {
                        id: null,
                        code: null,
                        name: null,
                        email: null,
                        phone: null,
                        address: null,
                        city: null,
                        state: null,
                        postal_code: null,
                        note: null,
                        is_customer: true,
                        is_supplier: false,
                        is_employee: false,
                        transportation_cost: null,
                    };
                    this.modal = 'add_contact';
                },

                openEditModal(contact) {
                    this.form = {
                        id: contact.id,
                        code: contact.code,
                        name: contact.name,
                        email: contact.email,
                        phone: contact.phone,
                        address: contact.address,
                        city: contact.city,
                        state: contact.state,
                        postal_code: contact.postal_code,
                        note: contact.note,
                        is_customer: !!contact.is_customer,
                        is_supplier: !!contact.is_supplier,
                        is_employee: !!contact.is_employee,
                        transportation_cost: contact.transportation_cost,
                    };
                    this.modal = 'edit_contact';
                },

                async handleCreate() {
                    Swal.fire({
                        title: 'Konfirmasi Pembuatan Kontak',
                        text: 'Apakah anda yakin ingin membuat kontak baru dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat kontak',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };
                            body.transportation_cost = this.n(body.transportation_cost);
                            Swal.fire({
                                title: 'Memproses penyimpanan Kontak...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.post(
                                    route('master.contacts.store'), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Kontak',
                        text: 'Apakah anda yakin ingin memperbarui kontak dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui kontak',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };
                            body.transportation_cost = this.n(body.transportation_cost);
                            Swal.fire({
                                title: 'Memproses perubahan Kontak...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.contacts.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                }
            };
        }

        function accountModule() {
            return {
                search: '',
                tableData: {
                    data: {}
                },
                loading: false,

                form: {
                    id: null,
                    code: null,
                    name: null,
                    category_id: null,
                    note: null
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.accounts.datatable'), {
                            params: {
                                search: this.search
                            }
                        });
                        this.tableData.data = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                handleSearch(q) {
                    this.search = q;
                    this.fetchData();
                },
                async handleStatus(id) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    try {
                        const r = await axios.post(route('master.accounts.status', id));
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                        await this.fetchData();
                    } catch (e) {
                        Swal.close();
                        Toast.fire({
                            icon: 'error',
                            title: e.response?.data?.message || 'Gagal mengubah status.'
                        });
                    }
                },

                openCreateModal() {
                    this.form = {
                        id: null,
                        code: null,
                        name: null,
                        category_id: null,
                        note: null
                    };
                    this.modal = 'add_account';
                },

                openEditModal(account) {
                    this.form = {
                        id: account.id,
                        code: account.code,
                        name: account.name,
                        category_id: account.category_id,
                        note: account.note
                    };
                    this.modal = 'edit_account';
                },

                async handleCreate() {
                    Swal.fire({
                        title: 'Konfirmasi Pembuatan Akun',
                        text: 'Apakah anda yakin ingin membuat akun baru dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat akun',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses penyimpanan Akun...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.post(
                                    route('master.accounts.store'), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Akun',
                        text: 'Apakah anda yakin ingin memperbarui akun dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui akun',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses perubahan Akun...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.accounts.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                }
            };
        }

        function warehouseModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 20,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 20,

                form: {
                    code: null,
                    name: null,
                    address: null,
                    note: null
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.warehouses.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data gudang.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                async handleStatus(g) {
                    Swal.fire({
                            title: g.deleted_at ? 'Aktifkan kembali gudang ini?' : 'Nonaktifkan gudang ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        })
                        .then(async result => {
                            if (!result.isConfirmed) return;
                            try {
                                const r = await axios.post(route('master.warehouses.status', g.id));
                                Toast.fire({
                                    icon: 'success',
                                    title: r.data.message
                                });
                                await this.fetchData();
                            } catch (e) {
                                Toast.fire({
                                    icon: 'error',
                                    title: e.response?.data?.message || 'Gagal mengubah status.'
                                });
                            }
                        });
                },
                openCreateModal() {
                    this.form = {
                        id: null,
                        code: null,
                        name: null,
                        address: null,
                        note: null
                    };
                    this.modal = 'add_warehouse';
                },

                openEditModal(warehouse) {
                    this.form = {
                        id: warehouse.id,
                        code: warehouse.code,
                        name: warehouse.name,
                        address: warehouse.address,
                        note: warehouse.note
                    };
                    this.modal = 'edit_warehouse';
                },

                async handleCreate() {
                    Swal.fire({
                        title: 'Konfirmasi Pembuatan Gudang',
                        text: 'Apakah anda yakin ingin membuat gudang baru dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat gudang',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses penyimpanan Gudang...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.post(
                                    route('master.warehouses.store'), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Gudang',
                        text: 'Apakah anda yakin ingin memperbarui gudang dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui gudang',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses perubahan Gudang...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.warehouses.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });

                            }
                        }
                    });
                }
            };
        }

        function userModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],
                modal: null,

                form: {
                    id: null,
                    username: null,
                    password: null,
                    password_confirmation: null,
                    contact_id: null,
                    role_id: null
                },

                changePasswordForm: {
                    current_password: null,
                    new_password: null,
                    new_password_confirmation: null
                },

                avatarMeta(name) {
                    const words = (name || '').trim().split(/\s+/).filter(Boolean);
                    const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');
                    let h = 0;
                    for (const c of (name || '')) h = ((h * 31) + c.charCodeAt(0)) & 0xFFFFFFFF;
                    const hue = ((h % 360) + 360) % 360;
                    return {
                        initials,
                        bg: 'oklch(0.92 0.04 ' + hue + ')',
                        fg: 'oklch(0.45 0.10 ' + hue + ')'
                    };
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.users.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data user.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                async handleStatus(u) {
                    Swal.fire({
                        title: u.deleted_at ? 'Aktifkan kembali user ini?' : 'Nonaktifkan user ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then(async result => {
                        if (!result.isConfirmed) return;
                        try {
                            const r = await axios.post(route('master.users.status', u.id));
                            Toast.fire({
                                icon: 'success',
                                title: r.data.message
                            });
                            await this.fetchData();
                        } catch (e) {
                            Toast.fire({
                                icon: 'error',
                                title: e.response?.data?.message || 'Gagal mengubah status.'
                            });
                        }
                    });
                },
                openCreateModal() {
                    this.form = {
                        id: null,
                        username: null,
                        password: null,
                        password_confirmation: null,
                        contact_id: null,
                        role_id: null
                    };
                    this.modal = 'add_user';
                },
                openEditModal(user) {
                    this.form = {
                        id: user.id,
                        username: user.username,
                        contact_id: user.contact_id,
                        role_id: user.role_id
                    };
                    this.modal = 'edit_user';
                },
                openChangePasswordModal(user) {
                    this.form = {
                        id: user.id,
                        username: user.username,
                        contact_id: user.contact_id,
                        role_id: user.role_id
                    };
                    this.changePasswordForm = {
                        current_password: null,
                        new_password: null,
                        new_password_confirmation: null
                    };
                    this.modal = 'change_password';
                },
                async handleCreate() {
                    Swal.fire({
                        title: 'Konfirmasi Pembuatan User',
                        text: 'Apakah anda yakin ingin membuat user baru dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat user',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses penyimpanan User...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            try {
                                const response = await axios.post(route('master.users.store'), {
                                    ...this.form
                                });
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
                                    html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors).flat().map(msg =>
                                            `<li>${msg}</li>`).join('') + '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title,
                                    html
                                });
                            }
                        }
                    });
                },
                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan User',
                        text: 'Apakah anda yakin ingin memperbarui user dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui user',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses perubahan User...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            try {
                                const response = await axios.put(route('master.users.update', this.form
                                    .id), {
                                    username: this.form.username,
                                    contact_id: this.form.contact_id,
                                    role_id: this.form.role_id
                                });
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
                                    html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors).flat().map(msg =>
                                            `<li>${msg}</li>`).join('') + '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title,
                                    html
                                });
                            }
                        }
                    });
                },
                async handlePasswordChange() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Password',
                        text: 'Apakah anda yakin ingin memperbarui password user dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui password',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses perubahan Password...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            try {
                                const response = await axios.put(route('master.users.password', this.form
                                    .id), {
                                    ...this.changePasswordForm
                                });
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
                                    html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors).flat().map(msg =>
                                            `<li>${msg}</li>`).join('') + '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title,
                                    html
                                });
                            }
                        }
                    });
                }
            };
        }


        function permitModule() {
            return {
                roles: @json($roleList),
                permissionTree: @json($permissionTree),
                selectedRole: null,
                activePermissions: [], // permission names for the selected role
                expandedModules: {},
                modal: null,
                saving: false,
                form: {
                    id: null,
                    name: ''
                },

                init() {
                    // expand all modules by default
                    for (const mKey of Object.keys(this.permissionTree)) {
                        this.expandedModules[mKey] = true;
                    }
                },

                // ── Labels ─────────────────────────────────────────────
                moduleLabel(key) {
                    const map = {
                        purchasing: 'Pembelian',
                        sales: 'Penjualan',
                        master: 'Master Data'
                    };
                    return map[key] || key.replace(/-/g, ' ');
                },
                resourceLabel(key) {
                    return key.replace(/-/g, ' ');
                },
                actionLabel(key) {
                    const map = {
                        view: 'Lihat',
                        create: 'Tambah',
                        edit: 'Edit',
                        delete: 'Hapus'
                    };
                    return map[key] || key;
                },

                // ── Role selection ──────────────────────────────────────
                selectRole(role) {
                    this.selectedRole = role;
                    this.activePermissions = [...role.permissions];
                },

                // ── Permission helpers ──────────────────────────────────
                permKey(mKey, rKey, action) {
                    return `${mKey}.${rKey}.${action}`;
                },
                hasPermission(mKey, rKey, action) {
                    return this.activePermissions.includes(this.permKey(mKey, rKey, action));
                },
                togglePermission(mKey, rKey, action) {
                    const key = this.permKey(mKey, rKey, action);
                    const idx = this.activePermissions.indexOf(key);
                    if (idx === -1) this.activePermissions.push(key);
                    else this.activePermissions.splice(idx, 1);
                },

                // module-level check / indeterminate
                isModuleChecked(mKey) {
                    const all = this._modulePermKeys(mKey);
                    return all.length > 0 && all.every(k => this.activePermissions.includes(k));
                },
                isModuleIndeterminate(mKey) {
                    const all = this._modulePermKeys(mKey);
                    const checked = all.filter(k => this.activePermissions.includes(k));
                    return checked.length > 0 && checked.length < all.length;
                },
                toggleModuleAll(mKey, checked) {
                    const all = this._modulePermKeys(mKey);
                    if (checked) {
                        all.forEach(k => {
                            if (!this.activePermissions.includes(k)) this.activePermissions.push(k);
                        });
                    } else {
                        this.activePermissions = this.activePermissions.filter(k => !all.includes(k));
                    }
                },
                _modulePermKeys(mKey) {
                    const module = this.permissionTree[mKey] || {};
                    return Object.entries(module).flatMap(([rKey, actions]) =>
                        actions.map(action => this.permKey(mKey, rKey, action))
                    );
                },

                toggleModule(mKey) {
                    this.expandedModules[mKey] = !this.expandedModules[mKey];
                },

                // ── Save permissions ────────────────────────────────────
                async savePermissions() {
                    this.saving = true;
                    try {
                        await axios.put(route('master.roles.updatePermissions', this.selectedRole.id), {
                            permissions: this.activePermissions,
                        });
                        // update local role entry
                        const role = this.roles.find(r => r.id === this.selectedRole.id);
                        if (role) role.permissions = [...this.activePermissions];
                        Toast.fire({
                            icon: 'success',
                            title: 'Hak akses berhasil disimpan'
                        });
                    } catch (e) {
                        Toast.fire({
                            icon: 'error',
                            title: e.response?.data?.message || 'Gagal menyimpan hak akses'
                        });
                    } finally {
                        this.saving = false;
                    }
                },

                // ── Modal handlers ──────────────────────────────────────
                openCreateModal() {
                    this.form = {
                        id: null,
                        name: ''
                    };
                    this.modal = 'add_role';
                },
                openEditModal(role) {
                    this.form = {
                        id: role.id,
                        name: role.name
                    };
                    this.modal = 'edit_role';
                },

                async handleCreate() {
                    try {
                        const r = await axios.post(route('master.roles.store'), {
                            name: this.form.name
                        });
                        this.modal = null;
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                        this.roles = (await axios.get(route('master.roles.index'))).data;
                    } catch (e) {
                        this._showError(e);
                    }
                },

                async handleUpdate() {
                    try {
                        const r = await axios.put(route('master.roles.update', this.form.id), {
                            name: this.form.name
                        });
                        this.modal = null;
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                        const role = this.roles.find(r => r.id === this.form.id);
                        if (role) role.name = this.form.name;
                        if (this.selectedRole && this.selectedRole.id === this.form.id) {
                            this.selectedRole.name = this.form.name;
                        }
                    } catch (e) {
                        this._showError(e);
                    }
                },

                _showError(error) {
                    let title = 'Terjadi kesalahan yang tidak terduga.',
                        html = null;
                    if (error.response?.status === 422) {
                        title = 'Validasi gagal. Periksa kembali input Anda.';
                        html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                            Object.values(error.response.data.errors).flat().map(m => `<li>${m}</li>`).join('') + '</ul>';
                    } else if (error.response?.data?.message) {
                        title = error.response.data.message;
                    }
                    Toast.fire({
                        icon: 'error',
                        title,
                        html
                    });
                },
            };
        }

        function accountSettingModule() {
            return {
                groups: @json($accountSettingGroups),
                allAccounts: @json($allAccounts),
                settings: {},

                init() {
                    const raw = @json($accountSettingValues);
                    this.settings = Object.fromEntries(
                        Object.entries(raw).map(([key, value]) => [key, value ?? ''])
                    );
                },

                async submitSettings() {
                    Swal.fire({
                        title: 'Memproses perubahan pengaturan akun...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    try {
                        const payload = {
                            settings: Object.entries(this.settings).map(([setting_key, account_id]) => ({
                                setting_key,
                                account_id: account_id === '' ? null : account_id,
                            })),
                        };
                        const r = await axios.put(route('master.account_settings.update'), payload);
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: r.data.message
                        });
                    } catch (error) {
                        Swal.close();
                        let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
                            html = null;
                        if (error.response?.status === 422) {
                            title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                            html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                Object.values(error.response.data.errors).flat().map(msg =>
                                    `<li>${msg}</li>`).join('') + '</ul>';
                        } else if (error.response?.data?.message) {
                            title = error.response.data.message;
                        }
                        Toast.fire({
                            icon: 'error',
                            title,
                            html
                        });
                    }
                },
            };
        }
    </script>
    <div x-data="masterPageData()" x-init="init()" x-on:open-edit-gudang.window="openEditGudang($event.detail)"
        class="master-page">

        <div class="master-hd">
            <div>
                <h1 class="order-title display">Master Data</h1>
                <div class="order-sub">Kelola data referensi untuk seluruh modul ERP</div>
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="utab">
            @foreach ([['produk', 'Produk'], ['gudang', 'Gudang'], ['kontak', 'Kontak'], ['user', 'User'], ['permit', 'Hak Akses'], ['akun', 'Akun'], ['account_setting', 'Pengaturan Akun']] as [$id, $lbl])
                <button class="utab-item"
                    x-on:click="tab = '{{ $id }}'; sessionStorage.setItem('master_tab', '{{ $id }}')"
                    :class="tab === '{{ $id }}' ? 'utab-active' : ''">{{ $lbl }}</button>
            @endforeach
        </div>

        @include('master.partials.tabs.product')
        @include('master.partials.tabs.warehouse')
        @include('master.partials.tabs.contact')
        @include('master.partials.tabs.user')
        @include('master.partials.tabs.permit')
        @include('master.partials.tabs.account')
        @include('master.partials.tabs.account-setting')

    </div>
@endsection
