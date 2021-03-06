import { faPortrait, faTachometerAlt, faTrashAlt, faUserEdit } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import { renderActionList } from '../../../helpers';
import { character, user } from '../../../vendor/data';

class Item extends PureComponent {
    render = () => {
        const { me, character, onDeleteHandler } = this.props;

        const characterSets = character.sets.map(set => (
            <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className="badge badge-dark">
                {set['name']}
            </a>
        ));

        character.actionList = {
            view: (
                <Link to={'/characters/' + character.id} title="Character Sheet">
                    <FontAwesomeIcon icon={faPortrait} />
                </Link>
            ),
            parses:
                me.id === character.owner.id && character['role'].indexOf('DD') !== -1 ? (
                    <Link to={'/@me/characters/' + character.id + '/parses'} title="DPS Parses">
                        <FontAwesomeIcon icon={faTachometerAlt} />
                    </Link>
                ) : null,
            edit:
                me.id === character.owner.id ? (
                    <Link to={'/@me/characters/' + character.id + '/edit'} title="Edit Character">
                        <FontAwesomeIcon icon={faUserEdit} />
                    </Link>
                ) : null,
            delete:
                typeof onDeleteHandler === 'function' && me.id === character.owner.id && character.approved_for_tier === 0 ? (
                    <Link to="#" onClick={onDeleteHandler} data-id={character.id} title="Delete Character">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        let rowBgColor = 'no-clearance';
        if (character['approved_for_tier'] !== 0) {
            rowBgColor = 'tier-' + character['approved_for_tier'];
        }

        return (
            <tr className={rowBgColor} key={'characterRow-' + character.id} data-id={character.id}>
                <td>
                    {character.name}
                    <br />
                    <small>
                        {character.class} / {character.role}
                    </small>
                </td>
                <td className="sets">{characterSets.reduce((prev, curr) => [prev, ' ', curr], [])}</td>
                <td>
                    <ul className="action-list">{renderActionList(character.actionList)}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    me: user,
    character,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
};

export default Item;
