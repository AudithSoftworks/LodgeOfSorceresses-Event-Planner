import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { normalize } from "normalizr";
import React from 'react';
import axios from "./axios";
import * as schema from './schema';

library.add(faDiscord);

export const getUser = (cancelToken) => axios.get('/api/users/@me', {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.user);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getUserGroups = (cancelToken) => axios.get('/api/groups', {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return response.data;
    }

    return null;
}).catch(error => {
    throw error;
});

export const getSets = (cancelToken) => axios.get('/api/sets', {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfSets);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getSkills = (cancelToken) => axios.get('/api/skills', {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfSkills);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getMyCharacters = (cancelToken) => axios.get('/api/characters', {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfCharacters);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getMyCharacter = (cancelToken, characterId) => axios.get('/api/characters/' + characterId, {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.character);
    }

    return null;
}).catch(error => {
    throw error;
});

export const postMyCharacter = (cancelToken, data) => axios.post('/api/characters', data, {
    cancelToken: cancelToken.token
}).then(
    response => response
).catch(error => {
    throw error;
});

export const putMyCharacter = (cancelToken, characterId, data) => axios.post('/api/characters/' + characterId, data, {
    cancelToken: cancelToken.token,
    headers: {
        'X-HTTP-Method-Override': 'PUT'
    }
}).then(
    response => response.status === 204
).catch(error => {
    throw error;
});

export const deleteMyCharacter = (cancelToken, characterId) => axios.delete('/api/characters/' + characterId, {
    cancelToken: cancelToken.token,
}).then(
    response => response.status === 204
).catch(error => {
    throw error;
});

export const getMyDpsParse = (cancelToken, characterId, parseId) => axios.get('/api/characters/' + characterId + '/parses/' + parseId, {
    cancelToken: cancelToken.token
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.dpsParse);
    }

    return null;
}).catch(error => {
    throw error;
});

export const postMyDpsParse = (cancelToken, characterId, data) => axios.post('/api/characters/' + characterId + '/parses', data, {
    cancelToken: cancelToken.token
}).then(
    response => response
).catch(error => {
    throw error;
});

export const putMyDpsParse = (cancelToken, characterId, parseId, data) => axios.post('/api/characters/' + characterId + '/parses/' + parseId, data, {
    cancelToken: cancelToken.token,
    headers: {
        'X-HTTP-Method-Override': 'PUT'
    }
}).then(
    response => response.status === 204
).catch(error => {
    throw error;
});

export const deleteMyDpsParse = (cancelToken, characterId, parseId) => {
    return axios.delete('/api/characters/' + characterId + '/parses/' + parseId, {
        cancelToken: cancelToken.token
    }).then(response => {
        if (response.data) {
            return normalize(response.data, schema.listOfDpsParses);
        }

        return null;
    }).catch(error => {
        throw error;
    });
};
