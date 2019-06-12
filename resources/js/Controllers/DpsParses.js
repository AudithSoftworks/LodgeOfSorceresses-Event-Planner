import { library } from '@fortawesome/fontawesome-svg-core';
import { faThList, faUserPlus } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import deleteMyDpsParseAction from '../actions/delete-my-dps-parse';
import { infosAction } from '../actions/notifications';
import List from '../Components/DpsParses/List';
import { characters, user } from '../vendor/data';
import Notification from './Notification';

library.add(faThList, faUserPlus);

class DpsParses extends PureComponent {
    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    }

    getCharacter = () => {
        const { match, myCharacters } = this.props;
        const characterId = match.params.id;

        return myCharacters.find(item => item.id === parseInt(characterId));
    };

    handleDelete = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this parse?')) {
            let currentTarget = event.currentTarget;
            const { match, deleteMyDpsParseAction } = this.props;
            const characterId = parseInt(match.params.id);
            const parseId = parseInt(currentTarget.getAttribute('data-id'));

            return deleteMyDpsParseAction(characterId, parseId);
        }
    };

    renderNotificationForNoDpsParses = character => {
        const { dispatch, notifications } = this.props;
        const dpsParses = character.dps_parses;
        if (dpsParses && !dpsParses.length && notifications.find(n => n.key === 'no-dps-parses-create-one') === undefined) {
            const message = [
                <Fragment key="f-1">Create a new parse, by clicking</Fragment>,
                <FontAwesomeIcon icon={['far', 'user-plus']} key="icon" />,
                <Fragment key="f-2">icon on top right corner.</Fragment>,
            ].reduce((prev, curr) => [prev, ' ', curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                    },
                    'no-dps-parses-create-one'
                )
            );
        }
    };

    renderWarningForLackingDiscordOauthAccount = associatedDiscordAccount => {
        let discordLinkWarning = null;
        if (associatedDiscordAccount && !associatedDiscordAccount.length) {
            discordLinkWarning = (
                <article className="alert-danger">
                    <b>Important note:</b>
                    <ul>
                        <li>
                            Your Parses will not be evaluated until <a href="/oauth/to/discord">you link your Discord account</a>!
                        </li>
                    </ul>
                </article>
            );
        }
        return discordLinkWarning;
    };

    renderActionList = () => {
        const actionList = {
            return: (
                <Link to={'/@me/characters'} title="Back to My Characters">
                    <FontAwesomeIcon icon={['far', 'th-list']} />
                </Link>
            ),
            create: (
                <Link to={'/@me/characters/' + this.props.match.params.id + '/parses/create'} title="Submit a Parse">
                    <FontAwesomeIcon icon={['far', 'user-plus']} />
                </Link>
            ),
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }
        return actionListRendered;
    };

    render = () => {
        const { me } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const character = this.getCharacter();
        if (!character) {
            return <Redirect to="/@me/characters" />;
        }
        this.renderNotificationForNoDpsParses(character);

        const dpsParses = character.dps_parses;

        const { associatedDiscordAccount } = this.props;
        const discordLinkWarning = this.renderWarningForLackingDiscordOauthAccount(associatedDiscordAccount);
        const actionListRendered = this.renderActionList();

        return [
            <section className="col-md-24 p-0 mb-4" key="dpsParsesList">
                <h2 className="form-title col-md-24">
                    Parses for <i>{character.name}</i> Pending Approval
                </h2>
                <ul className="ne-corner">{actionListRendered}</ul>
                {discordLinkWarning}
                <List character={character} dpsParses={dpsParses} onDeleteHandler={this.handleDelete} />
            </section>,
            <Notification key="notifications" />
        ];
    };
}

DpsParses.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    myCharacters: characters,
    notifications: PropTypes.array,

    deleteMyDpsParseAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteMyDpsParseAction: (characterId, parseId) => dispatch(deleteMyDpsParseAction(characterId, parseId)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(DpsParses);
