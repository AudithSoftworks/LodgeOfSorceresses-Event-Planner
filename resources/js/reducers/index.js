import { connectRouter } from 'connected-react-router';
import { combineReducers } from 'redux-immutable';
import contentReducer from './content';
import getAxiosCancelTokenSourceReducer from './get-axios-cancel-token-source';
import myCharactersReducer from './my-characters';
import notificationsReducer from './notifications';
import selectedCharacterReducer from "./selected-character";
import setsReducer from './sets';
import skillsReducer from './skills';
import teamsReducer from "./teams";
import userReducer from './user';

const rootReducer = history =>
    combineReducers({
        axiosCancelTokenSource: getAxiosCancelTokenSourceReducer,
        me: userReducer,
        sets: setsReducer,
        skills: skillsReducer,
        content: contentReducer,
        selectedCharacter: selectedCharacterReducer,
        myCharacters: myCharactersReducer,
        teams: teamsReducer,
        notifications: notificationsReducer,
        router: connectRouter(history),
    });

export default rootReducer;
