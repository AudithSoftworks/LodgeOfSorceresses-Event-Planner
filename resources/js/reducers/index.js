import { connectRouter } from 'connected-react-router';
import { combineReducers } from 'redux-immutable';
import contentReducer from './content';
import getAxiosCancelTokenSourceReducer from './get-axios-cancel-token-source';
import groupsReducer from './groups';
import myCharactersReducer from './my-characters';
import notificationsReducer from './notifications';
import setsReducer from './sets';
import skillsReducer from './skills';
import userReducer from './user';

const rootReducer = history =>
    combineReducers({
        axiosCancelTokenSource: getAxiosCancelTokenSourceReducer,
        me: userReducer,
        groups: groupsReducer,
        sets: setsReducer,
        skills: skillsReducer,
        content: contentReducer,
        myCharacters: myCharactersReducer,
        notifications: notificationsReducer,
        router: connectRouter(history),
    });

export default rootReducer;
