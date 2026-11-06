<template>
    <div class="form-group">
        <hr />
        <div style="margin-bottom: 10px">
            <label>
                <i class="fa fa-dollar" aria-hidden="true" style="margin-right: 5px"></i>
                {{ i18n.bank_information_for_payout }}
            </label>
        </div>

        <div class="input-bank-info row">
            <div class="col-sm-3 col-md-3">
                <span>{{ i18n.first_name }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" v-model="bank_info_first_name" name="bank_info_first_name"
                    :placeholder="i18n.first_name" />
            </div>
            <div class="col-sm-3 col-md-3">
                <span>{{ i18n.last_name }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" v-model="bank_info_last_name" name="bank_info_last_name"
                    :placeholder="i18n.last_name" />
            </div>
            <div class="col-sm-6 col-md-6">
                <span>{{ i18n.personal_address }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" v-model="bank_info_personal_address"
                    name="bank_info_personal_address" :placeholder="i18n.personal_address" />
            </div>
            <div class="col-sm-6 col-md-6">
                <span>{{ i18n.bank_address }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" v-model="bank_address" name="bank_address"
                    :placeholder="i18n.bank_address" />
            </div>
            <div class="col-sm-3 col-md-3">
                <span>{{ i18n.business_email }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" v-model="bank_info_business_email" name="bank_info_business_email"
                    :placeholder="i18n.business_email" />
            </div>
            <div class="col-sm-3 col-md-3">
                <span>{{ i18n.swift_code }}</span>
                <input type="text" class="form-control" v-model="bank_info_swift_code" name="bank_info_swift_code"
                    :placeholder="i18n.swift_code" />
            </div>
            <div class="col-sm-6 col-md-6">
                <span>{{ i18n.bank_routing_number }} <strong class="text-danger">*</strong></span>
                <input type="text" style="margin-right:10px;" class="form-control" name="bank_routing_number"
                    v-model="bank_routing_number" :placeholder="i18n.bank_routing_number" />
            </div>
            <div class="col-sm-6 col-md-6">
                <span>{{ i18n.bank_account_number }} <strong class="text-danger">*</strong></span>
                <input type="text" class="form-control" name="bank_account_number" v-model="bank_account_number"
                    :placeholder="i18n.bank_account_number" />
            </div>
        </div>
        <hr />
        <input type="hidden" name="is_subscription" :value="0" />
        <div>
            <label for="is_subscription">{{ i18n.enable_subscription }}&nbsp;</label>
            <input type="checkbox" v-model="new_is_subscription" />
            <input type="hidden" name="is_subscription" :value="new_is_subscription == 1 ? 1 : 0" />
            <div v-if="new_is_subscription" class="row">
                <div class="col-sm-6 col-md-6">
                    <span>{{ i18n.monthly_amount }}</span>
                    <input type="number" class="form-control" name="monthly_subscription_amount"
                        v-bind="monthly_subscription_amount" :placeholder="i18n.monthly_amount" required />
                </div>
                <div class="col-sm-6 col-md-6">
                    <span>{{ i18n.annual_amount }}</span>
                    <input type="number" class="form-control" name="yearly_subscription_amount"
                        v-bind="yearly_subscription_amount" :placeholder="i18n.annual_amount" required />
                </div>
                <div class="col-sm-12 col-md-12" v-if="org_create == 0">
                    {{ i18n.download_your_list_before_editing }}. <br />
                    {{ i18n.once_edit_your_subscribers_list_not_accessable }}.
                </div>
            </div>
        </div>
        <div style="margin-top:20px;">
            <label for="is_crypto">{{ i18n.enable_crypto_payment }}&nbsp;</label>
            <input type="checkbox" v-model="new_is_crypto" />
            <input type="hidden" name="is_crypto" :value="new_is_crypto == 1 ? 1 : 0" />
            <div v-if="new_is_crypto" class="row">
                <div class="col-sm-10 col-md-10">
                    <span>{{ i18n.wallet_address_etherum }}</span>
                    <input type="text" class="form-control" name="wallet_address" v-bind="wallet_address"
                        :placeholder="i18n.wallet_address_etherum" />
                </div>
            </div>
        </div>
        <hr />
        <label>{{ i18n.promo_code }}</label>
        <button class="btn btn-secondary" :disabled="generatingPromo" @click="generatePromo()" style="margin-left:30px;"
            type="button"><i class="fa fa-plus" style="padding-right:10px;"></i>{{ i18n.generate_new }}</button>
        <div v-for="(item, index) in promo_code" :key="index" style="margin-top:20px" class="row">
            <div class="col-md-2 col-sm-2" style="padding-right:5px!important">
                <input type="text" class="form-control" style="cursor:pointer;font-size:14px !important;" readonly
                    :value="item.promo_code" name="promo_code[]" />
            </div>
            <div class="col-md-3 col-sm-3" style="padding-right:5px!important;padding-left:5px!important">
                <input type="text" class="form-control" :placeholder="i18n.email" :value="item.affiliate_email" readonly
                    name="affiliate_email[]" />
            </div>
            <div class="col-md-2 col-sm-2 flex" style="padding-right:5px!important;padding-left:5px!important">
                <input type="number" class="form-control" :placeholder="i18n.percent" :value="item.affiliate_percent" readonly
                    name="affiliate_percent[]" />
                <span style="padding:5px;">%</span>
            </div>
            <div class="col-md-2 col-sm-2 flex" style="padding-right:5px!important;padding-left:5px!important">
                <input type="number" class="form-control" :placeholder="i18n.discount_percent" readonly
                    v-model="item.discount_percent" name="discount_percent[]" />
                <span style="padding:5px;">%</span>
            </div>
            <div class="flex" style="justify-content:end;padding-left:5px!important;padding-right:15px!important">
                <button class="btn btn-secondary" :disabled="sendAgainBtnDisabled" @click="sendAgain(item.id)"
                    style="margin-left:5px;" type="button">
                    {{ i18n.send_again }}
                </button>
                <button class="btn btn-secondary" @click="deletePromo(item.promo_code)"
                    :disabled="deletingPromoCode === item.promo_code" style="margin-left:5px;" type="button">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        <div v-for="(item, index) in promo_lists" :key="'new_' + index" style="margin-top:20px" class="row">
            <div class="col-md-2 col-sm-2" style="padding-right:5px!important">
                <input type="text" class="form-control" style="cursor:pointer;font-size:14px !important;"
                    v-model="item.promo_code" name="promo_code[]" @keyup="checkPromo(item.promo_code)" />
            </div>
            <div class="col-md-3 col-sm-3" style="padding-right:5px!important;padding-left:5px!important">
                <input type="text" class="form-control" :placeholder="i18n.email" name="affiliate_email[]"
                    v-model="item.affiliate_email" />
            </div>
            <div class="col-md-3 col-sm-3 flex" style="padding-right:5px!important;padding-left:5px!important">
                <span style="margin-right:5px;padding-top:7px;">{{ i18n.affiliate }}</span>
                <input type="number" size="4" maxlength="3" @change="changeHandlePercent(index)" class="form-control"
                    :placeholder="i18n.percent" v-model="item.affiliate_percent" name="affiliate_percent[]" />
                <span style="padding:7px;">%</span>
            </div>
            <div class="col-md-3 col-sm-3 flex" style="padding-right:5px!important;padding-left:5px!important">
                <span style="margin-right:5px;padding-top:7px;">{{ i18n.discount_percent }}</span>
                <input type="number" class="form-control" @change="changeHandlePercent(index)"
                    :placeholder="i18n.discount_percent" v-model="item.discount_percent" name="discount_percent[]" />
                <span style="padding:7px;">%</span>
            </div>
            <div class="flex" style="justify-content:end;padding-left:5px!important;padding-right:15px!important">
                <button class="btn btn-secondary" @click="deletePromo(item.promo_code)"
                    :disabled="deletingPromoCode === item.promo_code" style="margin-left:10px;" type="button">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="col-md-12 col-sm-12">
                <span v-if="item.error" style="color:red">{{ i18n.this_promo_code_in_use }}!</span>
            </div>
        </div>
        <button type="submit" id="submit-btn" class="btn btn-primary" style="margin-top:20px;float:right;">
            {{ org_create == 1 ? i18n.create_organization : i18n.update_organization }}
        </button>
    </div>
</template>

<script>
export default {
    props: [
        // "has_error_business_email",
        // "has_error_bank",

        "initial_bank_routing_number",
        "initial_bank_account_number",
        "initial_bank_address",
        "initial_bank_info_first_name",
        "initial_bank_info_last_name",
        "initial_bank_info_personal_address",
        "initial_bank_info_business_email",
        "initial_bank_info_swift_code",

        "org_create",

        "is_subscription",
        "monthly_subscription_amount",
        "yearly_subscription_amount",

        "is_crypto",
        "wallet_address",

        "promo_codes",
        "i18n"
    ],
    data() {
        return {
            bank_info_first_name: this.initial_bank_info_first_name,
            bank_info_last_name: this.initial_bank_info_last_name,
            bank_info_business_email: this.initial_bank_info_business_email,
            bank_routing_number: this.initial_bank_routing_number,
            bank_account_number: this.initial_bank_account_number,
            bank_address: this.initial_bank_address,
            bank_info_personal_address: this.initial_bank_info_personal_address,
            bank_info_swift_code: this.initial_bank_info_swift_code,

            sendAgainBtnDisabled: false,
            new_is_subscription: this.is_subscription == 1,
            new_is_crypto: this.is_crypto && this.is_crypto == 1,

            generatingPromo: false,
            promo_lists: [],
            promo_code: this.promo_codes,

            deletingPromoCode: null,
            typingPromoCodeTimeout: null,
        };
    },
    mounted() {
    },
    computed: {
    },
    methods: {
        generatePromo() {
            this.generatingPromo = true;
            axios
                .get("/api/organization/generate_promo")
                .then((res) => {
                    this.generatingPromo = false;
                    if (res.data && res.data.success) {
                        this.promo_lists.push({
                            promo_code: res.data.data,
                            affiliate_email: "",
                            affiliate_percent: 0,
                            discount_percent: 0,
                        });
                    }
                })
                .catch(() => {
                    this.generatingPromo = false;
                });
        },
        checkPromo(code) {
            if (this.typingPromoCodeTimeout) {
                clearTimeout(this.typingPromoCodeTimeout);
            }
            this.typingPromoCodeTimeout = setTimeout(function (scope) {
                axios.post(`/api/promo/check/${code}`).then((res) => {
                    if (res.data && res.data.success) {
                        const uniqueValues = new Set(scope.promo_lists.map(v => v.promo_code));
                        if (res.data.data || uniqueValues.size < scope.promo_lists.length) {
                            scope.promo_lists[scope.promo_lists.findLastIndex(el => el.promo_code === code)].error = true
                            scope.$set(scope.promo_lists, scope.promo_lists.findLastIndex(el => el.promo_code === code), scope.promo_lists[scope.promo_lists.findLastIndex(el => el.promo_code === code)])
                        }
                        else if (scope.promo_lists[scope.promo_lists.findIndex(el => el.promo_code === code)]) {
                            scope.promo_lists[scope.promo_lists.findLastIndex(el => el.promo_code === code)].error = null
                            scope.$set(scope.promo_lists, scope.promo_lists.findLastIndex(el => el.promo_code === code), scope.promo_lists[scope.promo_lists.findLastIndex(el => el.promo_code === code)])
                        }
                    }
                });
            }, 800, this);
        },
        deletePromo(code) {
            this.deletingPromoCode = code;
            axios.delete(`/api/promo/delete/${code}`).then((res) => {
                if (res.data && res.data.success) {
                    const list = this.promo_lists.filter((item) => item.promo_code !== code);
                    const codes = this.promo_code.filter((item) => item.promo_code !== code);
                    this.promo_code = JSON.parse(JSON.stringify(codes));
                    this.promo_lists = JSON.parse(JSON.stringify(list));
                }
                this.deletingPromoCode = null;
            }).catch(error => {
                this.deletingPromoCode = null;
            });
        },
        sendAgain(id) {
            axios.get(`/promo/send/${id}`).then((res) => {
                if (res.data && res.data.success) {
                    this.sendAgainBtnDisabled = true;
                }
            });
        },
        changeHandlePercent(_idx) {
            if (Number(this.promo_lists[_idx].affiliate_percent) > 100) {
                this.promo_lists[_idx].affiliate_percent = 100;
            }
            else if (Number(this.promo_lists[_idx].discount_percent) > 100) {
                this.promo_lists[_idx].discount_percent = 100;
            }
            else if (Number(this.promo_lists[_idx].affiliate_percent) < 0) {
                this.promo_lists[_idx].affiliate_percent = 0;
            }
            else if (Number(this.promo_lists[_idx].discount_percent) < 0) {
                this.promo_lists[_idx].discount_percent = 0;
            }
        },
    },
};
</script>

<style></style>
