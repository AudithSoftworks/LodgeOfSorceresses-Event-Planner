import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "my_characters-scss" */
    '../../../sass/_my_characters.scss'
);

import { faTachometerAlt, faUserPlus } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import deleteMyCharacterAction from '../../actions/delete-my-character';
import { infosAction } from '../../actions/notifications';
import List from '../../Components/Characters/List';
import { authorizeUser, renderActionList } from '../../helpers';
import { characters, user } from '../../vendor/data';
import Notification from '../../Components/Notification';

class Characters extends PureComponent {
    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    }

    handleDelete = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this character?')) {
            const currentTarget = event.currentTarget;
            const { deleteMyCharacterAction } = this.props;
            const characterId = parseInt(currentTarget.getAttribute('data-id'));

            return deleteMyCharacterAction(characterId);
        }
    };

    renderNoCharactersCreateOneNotification = () => {
        const { dispatch, myCharacters, notifications } = this.props;
        if (!myCharacters.length && notifications.find(n => n.key === 'no-characters-create-one') === undefined) {
            const message = [
                <Fragment key="f-1">Create a new character, by clicking</Fragment>,
                <FontAwesomeIcon icon={faUserPlus} key="icon" />,
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
                        width: 250,
                    },
                    'no-characters-create-one'
                )
            );
        }
    };

    render = () => {
        const { me, groups, location, myCharacters } = this.props;
        if (!me || !myCharacters) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        if (me && groups && !authorizeUser(this.props, true)) {
            return <Redirect to='/' />;
        }

        const actionList = {
            create: (
                <Link to="/@me/characters/create" className="ne-corner" title="Submit a Character">
                    <FontAwesomeIcon icon={faUserPlus} />
                </Link>
            ),
        };
        this.renderNoCharactersCreateOneNotification();

        return [
            <section className="col-md-24 p-0 mb-4 table-responsive" key="characterList">
                <h2 className="form-title col-md-24" title="My Characters">
                    My Characters
                </h2>
                <article className="alert-info">
                    <b>Usage tips:</b>
                    <ul>
                        <li>Mouse-over the character name for action buttons to reveal to the right of row.</li>
                        <li>
                            Only Damage Dealers can submit DPS-parses. Click <FontAwesomeIcon icon={faTachometerAlt} /> icon to the right to create one for such Character.
                        </li>
                        <li>
                            When creating a Character, select <b>all</b> your available sets.
                        </li>
                        <li>To have different Roles for the same Character, create a new Character with the same name, but a different Role.</li>
                        <li>Once a Character has an approved Parse, it can only be partially edited (i.e. its name, class and role can't be edited).</li>
                        <li>Once a Character has a Clearance (Tier-1 and above), it cannot be deleted.</li>
                    </ul>
                </article>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <List characters={myCharacters} onDeleteHandler={this.handleDelete} className="my-character-list-table" />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

Characters.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    myCharacters: characters,
    notifications: PropTypes.array,

    deleteMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
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
