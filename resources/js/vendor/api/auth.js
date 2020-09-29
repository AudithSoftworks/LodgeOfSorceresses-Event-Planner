import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { normalize } from 'normalizr';
import axios from '../axios';
import * as schema from '../schema';

library.add(faDiscord);

export const finalizeOnboarding = (cancelToken, data) =>
    axios
        .post('/api/onboarding/finalize', data, {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.user);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const getUser = cancelToken =>
    axios
        .get('/api/users/@me', {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.user);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const putUser = (cancelToken, data) =>
    axios
        .post('/api/users/@me', data, {
            cancelToken: cancelToken.token,
            headers: {
                'X-HTTP-Method-Override': 'PUT',
            },
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.user);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const deleteUser = cancelToken =>
    axios
        .delete('/api/users/@me', {
            cancelToken: cancelToken.token,
        })
        .then(response => response.status === 204)
        .catch(error => {
            throw error;
        });

export const getMyCharacters = cancelToken =>
    axios
        .get('/api/users/@me/characters', {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.listOfCharacters);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const postMyCharacter = (cancelToken, data) =>
    axios
        .post('/api/users/@me/characters', data, {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.status === 201 && response.data) {
                return normalize(response.data, schema.character);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const putMyCharacter = (cancelToken, characterId, data) =>
    axios
        .post('/api/users/@me/characters/' + characterId, data, {
            cancelToken: cancelToken.token,
            headers: {
                'X-HTTP-Method-Override': 'PUT',
            },
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.character);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const deleteMyCharacter = (cancelToken, characterId) =>
    axios
        .delete('/api/users/@me/characters/' + characterId, {
            cancelToken: cancelToken.token,
        })
        .then(response => response.status === 204)
        .catch(error => {
            throw error;
        });

export const postMyDpsParse = (cancelToken, characterId, data) =>
    axios
        .post('/api/users/@me/characters/' + characterId + '/dps_parses', data, {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.character);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });

export const deleteMyDpsParse = (cancelToken, characterId, parseId) =>
    axios
        .delete('/api/users/@me/characters/' + characterId + '/dps_parses/' + parseId, {
            cancelToken: cancelToken.token,
        })
        .then(response => {
            if (response.data) {
                return normalize(response.data, schema.listOfDpsParses);
            }

            return null;
        })
        .catch(error => {
            throw error;
        });
