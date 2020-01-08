import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { normalize } from 'normalizr';
import React from 'react';
import axios from './axios';
import * as schema from './schema';

library.add(faDiscord);

export const getUserGroups = cancelToken => axios.get('/api/groups', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return response.data;
    }

    return null;
}).catch(error => {
    throw error;
});

export const getSets = cancelToken => axios.get('/api/sets', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfSets);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getSkills = cancelToken => axios.get('/api/skills', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfSkills);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getContent = cancelToken => axios.get('/api/content', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfContent);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getAllUsers = cancelToken => axios.get('/api/users', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfUsers);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getUser = (cancelToken, userId) => axios.get('/api/users/' + userId, {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.user);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getAllCharacters = cancelToken => axios.get('/api/characters', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfCharacters);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getCharacter = (cancelToken, characterId) => axios.get('/api/characters/' + characterId, {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.character);
    }

    return null;
}).catch(error => {
    throw error;
});

export const getTeams = cancelToken => axios.get('/api/teams', {
    cancelToken: cancelToken.token,
}).then(response => {
    if (response.data) {
        return normalize(response.data, schema.listOfTeams)
    }

    return null;
});

export const putTeam = (cancelToken, teamId, data) => axios.post('/api/teams/' + teamId, data, {
    cancelToken: cancelToken.token,
    headers: {
        'X-HTTP-Method-Override': 'PUT',
    },
}).then(
    response => {
        if (response.data) {
            return normalize(response.data, schema.team)
        }

        return null;
    }

).catch(error => {
    throw error;
});

export const postTeam = (cancelToken, data) => axios.post('/api/teams', data, {
    cancelToken: cancelToken.token,
}).then(
    response => {
        if (response.data) {
            return normalize(response.data, schema.team)
        }

        return null;
    }
).catch(error => {
    throw error;
});

export const deleteTeam = (cancelToken, teamId) => axios.delete('/api/teams/' + teamId, {
    cancelToken: cancelToken.token,
}).then(
    response => response.status === 204
).catch(error => {
    throw error;
});
