import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { normalize } from 'normalizr';
import React from 'react';
import axios from '../axios';
import * as schema from '../schema';

library.add(faDiscord);

export const updateCharacter = (cancelToken, characterId, data) => axios.post('/api/admin/characters/' + characterId, data, {
    cancelToken: cancelToken.token,
    headers: {
        'X-HTTP-Method-Override': 'PUT',
    },
}).then(
    response => response
).catch(error => {
    throw error;
});

export const getPendingDpsParses = cancelToken => axios.get('/api/admin/parses', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfDpsParses);
    }

    return null;
}).catch(error => {
    throw error;
});

export const updatePendingDpsParse = (cancelToken, parseId) => axios.post('/api/admin/parses/' + parseId, null, {
    cancelToken: cancelToken.token,
    headers: {
        'X-HTTP-Method-Override': 'PUT',
    },
}).then(
    response => response
).catch(error => {
    throw error;
});

export const deletePendingDpsParse = (cancelToken, parseId, reasonForDisapproval) => axios.delete('/api/admin/parses/' + parseId, {
    cancelToken: cancelToken.token,
    data: {
        reason_for_disapproval: reasonForDisapproval,
    },
}).then(
    response => response.status === 204
).catch(error => {
    throw error;
});
