import * as destroyActions from '../actions/delete-my-character';
import * as getActions from '../actions/get-character';
import * as indexActions from '../actions/get-my-characters';
import * as showDpsParseActions from '../actions/get-my-dps-parse';
import * as destroyDpsParseActions from '../actions/delete-my-dps-parse';

const myCharactersReducer = (state = null, action) => {
    if (action.type === indexActions.TYPE_GET_MY_CHARACTERS_SUCCESS) {
        let newState = undefined;
        if (action.response.result.length) {
            newState = [];
            Object.keys(action.response.entities.characters).forEach(key => {
                newState.push(action.response.entities.characters[key]);
            });
        } else {
            newState = [];
        }

        return newState;
    } else if (action.type === getActions.TYPE_GET_CHARACTER_SUCCESS) {
        const newState = state === null ? [] : [...state];
        const characterId = action.characterId;
        const indexOfCharacterUpdatedInStore = newState.findIndex(c => c.id === parseInt(characterId));
        if (indexOfCharacterUpdatedInStore !== -1) {
            newState.splice(indexOfCharacterUpdatedInStore, 1, action.response.entities.characters[characterId]);
        } else {
            newState.push(action.response.entities.characters[characterId]);
        }

        return newState;
    } else if (action.type === destroyActions.TYPE_DELETE_MY_CHARACTER_SUCCESS) {
        let newState = state === null ? [] : [...state];
        const characterId = action.characterId;
        newState = newState.filter(item => item.id !== characterId);

        return newState;
    }

    if (action.type === showDpsParseActions.TYPE_GET_MY_DPS_PARSE_SUCCESS) {
        const newState = state === null ? [] : [...state];
        const { characterId, parseId } = action;
        const indexOfCharacterDpsSubmittedFor = newState.findIndex(c => c.id === parseInt(characterId));
        const characterDpsSubmittedFor = newState.find(c => c.id === parseInt(characterId));
        if (characterDpsSubmittedFor) {
            characterDpsSubmittedFor.dps_parses_pending.push(action.response.entities.dpsParses[parseId]); // We only store. No edits!
            newState.splice(indexOfCharacterDpsSubmittedFor, 1);
            newState.push(characterDpsSubmittedFor);
        }

        return newState;
    } else if (action.type === destroyDpsParseActions.TYPE_DELETE_MY_DPS_PARSE_SUCCESS) {
        const newState = state === null ? [] : [...state];
        const characterId = action.characterId;
        const parseId = action.parseId;
        const character = newState.find(item => item.id === characterId);
        character.dps_parses_pending = character.dps_parses_pending.filter(item => item.id !== parseId);

        return newState;
    }

    return state;
};

export default myCharactersReducer;
