import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "characters-list-table-scss" */
    '../../../sass/_character-list-table.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { authorizeTeamManager } from "../../helpers";
import { teams, user } from '../../vendor/data';
import Item from '../Teams/List/Item';

class List extends PureComponent {
    render = () => {
        const { authorizedAsAdmin, me, teams, className, deleteTeamHandler } = this.props;
        let teamsRendered = teams
            .sort((a, b) => {
                const aNameLower = a.name.toLowerCase();
                const bNameLower = b.name.toLowerCase();
                if (aNameLower < bNameLower) {
                    return -1;
                } else if (aNameLower > bNameLower) {
                    return 1;
                }

                return 0;
            })
            .map(team => {
                const authorizedTeamManager = authorizeTeamManager({ me, team, authorizedAsAdmin });
                return <Item key={team.id}
                             authorizedTeamManager={authorizedTeamManager}
                             team={team}
                             deleteTeamHandler={deleteTeamHandler} />
            });
        if (teamsRendered.length) {
            teamsRendered = [
                <table key="character-list-table" className={'pl-2 pr-2 col-md-24 character-list-table ' + className}>
                    <thead>
                        <tr>
                            <th scope="col">Name, Tier/# of Members</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{teamsRendered}</tbody>
                </table>,
            ];
        }

        return teamsRendered;
    };
}

List.propTypes = {
    authorizedAsAdmin: PropTypes.bool.isRequired,
    className: PropTypes.string,
    deleteTeamHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
    me: user.isRequired,
    teams: teams.isRequired,
};

export default List;
