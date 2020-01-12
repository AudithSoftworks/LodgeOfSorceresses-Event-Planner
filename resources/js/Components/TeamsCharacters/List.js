import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "team-member-list-table-scss" */
    '../../../sass/_team-member-list-table.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { team } from '../../vendor/data';
import Item from './List/Item';

class List extends PureComponent {
    render = () => {
        const { className, deleteTeamMembershipHandler, team } = this.props;
        let teamMembersRendered = team.members
            .map(character => <Item key={character.id}
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
    className: PropTypes.string,
    deleteTeamMembershipHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
    team: team.isRequired,
};

export default List;
