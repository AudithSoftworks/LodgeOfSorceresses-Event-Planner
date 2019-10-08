import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "characters-list-table-scss" */
    '../../../sass/_character-list-table.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { characters, user } from '../../vendor/data';
import Item from '../Characters/List/Item';

class List extends PureComponent {
    render = () => {
        const { characters, className, me, onDeleteHandler } = this.props;
        let charactersRendered = characters
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
            .map(character => <Item key={character.id} character={character} me={me} onDeleteHandler={onDeleteHandler} />);
        if (charactersRendered.length) {
            charactersRendered = [
                <table key="character-list-table" className={'pl-2 pr-2 col-md-24 character-list-table ' + className}>
                    <thead>
                        <tr>
                            <th scope="col">Name, Class/Role</th>
                            <th scope="col">Sets</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{charactersRendered}</tbody>
                </table>,
            ];
        }

        return charactersRendered;
    };
}

List.propTypes = {
    className: PropTypes.string,

    me: user,
    characters,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
};

export default List;
