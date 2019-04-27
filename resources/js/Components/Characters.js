import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "characters-scss" */
    '../../sass/global/_characters.scss'
);

import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import deleteMyCharacterAction from '../actions/delete-my-character';
import { infosAction } from '../actions/notifications';
import { characters, user } from '../vendor/data';
import Notification from './Notification';

library.add(faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus);

class Characters extends PureComponent {
    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    }

    handleDelete = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this character?')) {
            let currentTarget = event.currentTarget;
            const { deleteMyCharacterAction } = this.props;
            const characterId = parseInt(currentTarget.getAttribute('data-id'));

            return deleteMyCharacterAction(characterId);
        }
    };

    renderListItem = character => {
        const characterSets = character.sets.map(set => (
            <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className="badge badge-dark">
                {set['name']}
            </a>
        ));
        character.actionList = {
            parses:
                character['role'].indexOf('Damage') !== -1 ? (
                    <Link to={'/characters/' + character.id + '/parses'} title="DPS Parses">
                        <FontAwesomeIcon icon={['far', 'tachometer-alt']} />
                    </Link>
                ) : null,
            edit: (
                <Link to={'/characters/' + character.id + '/edit'} title="Edit Character">
                    <FontAwesomeIcon icon={['far', 'user-edit']} />
                </Link>
            ),
            delete:
                !character.approved_for_midgame && !character.approved_for_endgame_t0 && !character.approved_for_endgame_t1 && !character.approved_for_endgame_t2 ? (
                    <Link to="#" onClick={this.handleDelete} data-id={character.id} title="Delete Character">
                        <FontAwesomeIcon icon={['far', 'trash-alt']} />
                    </Link>
                ) : null,
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(character.actionList)) {
            if (link) {
                actionListRendered.push(<li key={actionType}>{link}</li>);
            }
        }
        let rowBgColor = 'no_clearance';
        if (character['approved_for_endgame_t2']) {
            rowBgColor = 'endgame_tier_2';
        } else if (character['approved_for_endgame_t1']) {
            rowBgColor = 'endgame_tier_1';
        } else if (character['approved_for_endgame_t0']) {
            rowBgColor = 'endgame_tier_0';
        } else if (character['approved_for_midgame']) {
            rowBgColor = 'midgame';
        }

        return (
            <tr className={rowBgColor} key={'characterRow-' + character.id} data-id={character.id}>
                <td>{character.name}</td>
                <td>{character.class}</td>
                <td>{character.role}</td>
                <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td>
                    <ul className="actionList">{actionListRendered}</ul>
                </td>
            </tr>
        );
    };

    renderList = characters => {
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
            .map(character => this.renderListItem(character));
        if (charactersRendered.length) {
            charactersRendered = [
                <table key="character-list-table" className="pl-2 pr-2 col-md-24">
                    <thead>
                        <tr>
                            <th style={{ width: '20%' }}>Name</th>
                            <th style={{ width: '10%' }}>Class</th>
                            <th style={{ width: '20%' }}>Role</th>
                            <th style={{ width: '40%' }}>Sets</th>
                            <th style={{ width: '10%' }} />
                        </tr>
                    </thead>
                    <tbody>{charactersRendered}</tbody>
                </table>,
            ];
        }

        const actionList = {
            create: (
                <Link to="/characters/create" className="ne-corner" title="Submit a Character">
                    <FontAwesomeIcon icon={['far', 'user-plus']} />
                </Link>
            ),
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="characterList">
                <h2 className="form-title col-md-24">My Characters</h2>
                <article className="alert-info">
                    <b>Usage tips:</b>
                    <ul>
                        <li>Mouse-over the character name for action buttons to reveal to the right of row.</li>
                        <li>Only Damage Dealers can submit DPS-parses. Click <FontAwesomeIcon icon={['far', 'tachometer-alt']} /> icon to the right to create one for such Character.</li>
                        <li>When creating a Character, select <b>all</b> your available sets.</li>
                        <li>To have different Roles for the same Character, create a new Character with the same name, but a different Role.</li>
                        <li>Once a Character has an approved Parse, it can only be partially edited (i.e. only its gear-sets can be edited).</li>
                        <li>Once a Character has a clearance (Mid- or Endgame one), it cannot be deleted.</li>
                    </ul>
                </article>
                <ul className="ne-corner">{actionListRendered}</ul>
                {charactersRendered}
            </section>,
        ];
    };

    renderNoCharactersCreateOneNotification = () => {
        const { dispatch, myCharacters, notifications } = this.props;
        if (!myCharacters.length && notifications.find(n => n.key === 'no-characters-create-one') === undefined) {
            const message = [
                <Fragment key="f-1">Create a new character, by clicking</Fragment>,
                <FontAwesomeIcon icon={['far', 'user-plus']} key="icon" />,
                <Fragment key="f-2">icon on top right corner.</Fragment>,
            ].reduce((acc, curr) => [acc, ' ', curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                    },
                    'no-characters-create-one'
                )
            );
        }
    };

    render = () => {
        const { me, location, myCharacters } = this.props;
        if (me === null || myCharacters === null) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        this.renderNoCharactersCreateOneNotification();

        return [...this.renderList(myCharacters), <Notification key="notifications" />];
    };
}

Characters.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    myCharacters: characters,
    notifications: PropTypes.array,

    deleteMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteMyCharacterAction: characterId => dispatch(deleteMyCharacterAction(characterId)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Characters);
