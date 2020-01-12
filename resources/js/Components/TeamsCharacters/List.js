import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "team-member-list-table-scss" */
    '../../../sass/_team-member-list-table.scss'
);

import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { team, user } from '../../vendor/data';
import Item from './List/Item';

class List extends Component {
    render = () => {
        const { authorizedTeamManager, className, deleteTeamMembershipHandler, me, team } = this.props;
        let teamMembersRendered = team.members
            .map(character => <Item key={character.id}
                                    authorizedTeamManager={authorizedTeamManager}
                                    me={me}
                                    team={team}
                                    character={character}
                                    deleteTeamMembershipHandler={deleteTeamMembershipHandler} />);
        if (teamMembersRendered.length) {
            teamMembersRendered = [
                <table key="teams-characters-list-table" className={'pl-2 pr-2 col-md-24 teams-characters-list-table ' + className}>
                    <thead>
                        <tr>
                            <th scope="col">ESO ID</th>
                            <th scope="col">Character Name</th>
                            <th scope="col">Role/Class/Clearance</th>
                            <th scope="col">Status</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{teamMembersRendered}</tbody>
                </table>,
            ];
        }

        return teamMembersRendered;
    };
}

List.propTypes = {
    authorizedTeamManager: PropTypes.bool.isRequired,
    className: PropTypes.string,
    deleteTeamMembershipHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
    me: user.isRequired,
    team: team.isRequired,
};

export default List;
