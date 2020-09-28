import * as destroyAction from '../actions/delete-team';
import * as indexAction from '../actions/get-teams';
import * as postAction from '../actions/post-team';
import * as putAction from '../actions/put-team';
import * as postTeamsCharactersAction from '../actions/post-teams-characters';
import * as putTeamsCharactersAction from '../actions/put-teams-characters';
import * as destroyTeamsCharactersAction from '../actions/delete-teams-characters';

const teamsReducer = (state = null, action) => {
    if (action.type === indexAction.TYPE_GET_TEAMS_SUCCESS) {
        const newState = [];
        if (action.response && action.response.result.length) {
            Object.keys(action.response.entities['teams']).forEach(key => {
                newState.push(action.response.entities['teams'][key]);
            });
        }

        return newState;
    } else if (action.type === postAction.TYPE_POST_TEAM_SUCCESS || action.type === putAction.TYPE_PUT_TEAM_SUCCESS) {
        const newState = state === null ? [] : [...state];
        if (action.response) {
            const team = action.response.entities['teams'][action.response.result];
            const indexOfTeamUpdatedInStore = newState.findIndex(t => t.id === team.id);
            if (indexOfTeamUpdatedInStore !== -1) {
                newState.splice(indexOfTeamUpdatedInStore, 1, team);
            } else {
                newState.push(team);
            }
        }

        return newState;
    } else if (action.type === destroyAction.TYPE_DELETE_TEAM_SUCCESS) {
        let newState = state === null ? [] : [...state];
        const teamId = action.teamId;
        newState = newState.filter(item => item.id !== teamId);

        return newState;
    }

    if (action.type === postTeamsCharactersAction.TYPE_POST_TEAMS_CHARACTERS_SUCCESS || action.type === putTeamsCharactersAction.TYPE_PUT_TEAMS_CHARACTERS_SUCCESS) {
        const newState = state === null ? [] : [...state];
        if (action.response) {
            const team = action.response.entities['teams'][action.response.result];
            const indexOfTeamUpdatedInStore = newState.findIndex(t => t.id === team.id);
            if (indexOfTeamUpdatedInStore !== -1) {
                newState.splice(indexOfTeamUpdatedInStore, 1, team);
            }
        }

        return newState;
    } else if (action.type === destroyTeamsCharactersAction.TYPE_DELETE_TEAMS_CHARACTERS_SUCCESS) {
        const newState = state === null ? [] : [...state];
        const teamId = action.teamId;
        const characterId = action.characterId;
        const team = newState.find(t => t.id === teamId);
        team.members = team.members.filter(c => c.id !== characterId);

        return newState;
    }

    return state;
};

export default teamsReducer;
