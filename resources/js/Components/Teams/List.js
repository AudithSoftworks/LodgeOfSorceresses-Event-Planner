import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "characters-list-table-scss" */
    '../../../sass/_character-list-table.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { teams, user } from '../../vendor/data';
import Item from '../Teams/List/Item';

class List extends PureComponent {
    render = () => {
        const { teams, className, me, authorizedAsAdmin, deleteHandler } = this.props;
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
            .map(team => <Item key={team.id} team={team} me={me} authorizedAsAdmin={authorizedAsAdmin} deleteHandler={deleteHandler} />);
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
    className: PropTypes.string,

    me: user,
    authorizedAsAdmin: PropTypes.bool,
    teams,
    deleteHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
};

export default List;
