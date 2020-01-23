import Vue from "vue";
import ApiService from "@/common/api.service";

import {
    ##UUTemplate##_READ,
    ##UUTemplate##_UPDATE,
    ##UUTemplate##_CREATE,
    ##UUTemplate##_DELETE,
    ##UUTemplate##_CLEAR,
    ##UUTemplate##_LIST_READ,
    ##UUTemplate##_LIST_CLEAR
} from "./actions.type";

import {
    ##UUTemplate##_SET,
    ##UUTemplate##_LIST_SET
} from "./mutations.type";

const initialState = {
    ##Template##_list: [],
    ##Template##: {}
};

const getters = {
    ##Template##_list(state) {
        return state.##Template##_list;
    },
    ##Template##(state) {
        return state.##Template##;
    }
};

export const state = { ...initialState };

export const actions = {
    async [##UUTemplate##_LIST_READ](context) {
        return new Promise(resolve => {
          ApiService.get('##Template##')
            .then(({ data }) => {
                context.commit(##UUTemplate##_LIST_SET, data);
                resolve(data);
            });
        });
    },

    async [##UUTemplate##_READ](context, id) {
        return ApiService.get('##Template##/' + id)
            .then(({ data }) => {
                context.commit(##UUTemplate##_SET, data.##Template##);
            });
    },

    async [##UUTemplate##_DELETE](context, id) {
        await ApiService.delete('##Template##/' + id);
        context.dispatch(##UUTemplate##_LIST_READ);
    },

    async [##UUTemplate##_UPDATE](context, params) {
        return ApiService.update('##Template##', params.id, {
            ##Template##: params
        });
    },

    async [##UUTemplate##_CREATE](context, params) {
        await ApiService.post('##Template##', {
            ##Template##: params
        });
        context.commit(##UUTemplate##_SET, {});
    },

    [##UUTemplate##_CLEAR](context) {
        context.commit(##UUTemplate##_SET, {});
    },

    [##UUTemplate##_LIST_CLEAR](context) {
        context.commit(##UUTemplate##_LIST_SET, {});
    },
};

/* eslint no-param-reassign: ["error", { "props": false }] */
export const mutations = {
    [##UUTemplate##_LIST_SET](state, data) {
        state.##Template##_list = data;
    },

    [##UUTemplate##_SET](state, data) {
        state.##Template## = data;
    }
};

export default {
  state,
  actions,
  mutations,
  getters
};
