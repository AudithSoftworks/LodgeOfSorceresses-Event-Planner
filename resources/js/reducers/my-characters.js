import * as destroyActions from '../actions/delete-my-character';
import * as destroyDpsParseActions from '../actions/delete-my-dps-parse';
import * as indexActions from '../actions/get-my-characters';
import * as postCharacterActions from '../actions/post-my-character';
import * as putCharacterActions from '../actions/put-my-character';
import * as postDpsParseActions from '../actions/post-my-dps-parse';

const myCharactersReducer = (state = null, action) => {
    if (action.type === indexActions.TYPE_GET_MY_CHARACTERS_SUCCESS) {
        const newState = [];
        if (action.response.result.length) {
            Object.keys(action.response.entities.characters).forEach(key => {
                newState.push(action.response.entities['characters'][key]);
            });
        }

        return newState;
    } else if (
        action.type === postCharacterActions.TYPE_POST_MY_CHARACTER_SUCCESS
        || action.type === putCharacterActions.TYPE_PUT_MY_CHARACTER_SUCCESS
        || action.type === postDpsParseActions.TYPE_POST_MY_DPS_PARSE_SUCCESS
    ) {
        const newState = state === null ? [] : [...state];
        const character = action.response.entities['characters'][action.response.result];
        const indexOfCharacterUpdatedInStore = newState.findIndex(c => c.id === character.id);
        if (indexOfCharacterUpdatedInStore !== -1) {
            newState.splice(indexOfCharacterUpdatedInStore, 1, character);
        } else {
            newState.push(character);
        }

        return newState;
    } else if (action.type === destroyActions.TYPE_DELETE_MY_CHARACTER_SUCCESS) {
        let newState = state === null ? [] : [...state];
        const characterId = action.characterId;
        newState = newState.filter(item => item.id !== characterId);

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
