import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { characters } from '../../vendor/data';
import Item from '../Characters/Item';

class List extends PureComponent {
    render = () => {
        const { characters, className, onDeleteHandler } = this.props;
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
            .map(character => <Item key={character.id} character={character} onDeleteHandler={onDeleteHandler} />);
        if (charactersRendered.length) {
            charactersRendered = [
                <table key="character-list-table" className={'pl-2 pr-2 col-md-24 ' + className}>
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Class</th>
                            <th scope="col">Role</th>
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

    characters,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button inside <Item>
};

export default List;
