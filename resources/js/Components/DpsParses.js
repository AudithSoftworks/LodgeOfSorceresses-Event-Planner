import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "dps-parses-scss" */
    '../../sass/_my_dps_parses.scss'
    );

import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faThList, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import deleteMyDpsParseAction from '../actions/delete-my-dps-parse';
import { infosAction } from '../actions/notifications';
import { characters, user } from '../vendor/data';
import Notification from './Notification';

library.add(faSpinner, faTachometerAlt, faThList, faTrashAlt, faUserEdit, faUserPlus);

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

    renderListItem = (dpsParse, character) => {
        const characterSets = character.sets.map(set => (
            <a key={set.id} href={'https://eso-sets.com/set/' + set.id} className="badge badge-dark">
                {set.name}
            </a>
        ));
        dpsParse.actionList = {
            delete: (
                <Link to="#" onClick={this.handleDelete} data-id={dpsParse.id} title="Delete this Parse">
                    <FontAwesomeIcon icon={['far', 'trash-alt']} />
                </Link>
            ),
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(dpsParse.actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        return (
            <tr key={'dpsParseRow-' + dpsParse.id}>
                <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td>{dpsParse.dps_amount}</td>
                <td>
                    <a href={dpsParse.parse_file_hash.large} target="_blank">
                        <img src={dpsParse.parse_file_hash.thumbnail} alt="Parse screenshot" />
                    </a>
                </td>
                <td>
                    <a href={dpsParse.superstar_file_hash.large} target="_blank">
                        <img src={dpsParse.superstar_file_hash.thumbnail} alt="Superstar screenshot" />
                    </a>
                    <ul className="actionList">{actionListRendered}</ul>
                </td>
            </tr>
        );
    };

    renderList = (dpsParses, character) => {
        let parsesRendered = dpsParses.map(dpsParse => this.renderListItem(dpsParse, character));
        if (parsesRendered.length) {
            parsesRendered = [
                <table key="my-dps-parses-table" className="pl-2 pr-2 col-md-24">
                    <thead>
                        <tr>
                            <th scope='col'>Sets</th>
                            <th scope='col'>DPS Number</th>
                            <th scope='col'>Parse Screenshot</th>
                            <th scope='col'>Superstar Screenshot</th>
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>,
            ];
        }

        const actionList = {
            return: (
                <Link to={'/characters'} title="Back to My Characters">
                    <FontAwesomeIcon icon={['far', 'th-list']} />
                </Link>
            ),
            create: (
                <Link to={'/characters/' + this.props.match.params.id + '/parses/create'} title="Submit a Parse">
                    <FontAwesomeIcon icon={['far', 'user-plus']} />
                </Link>
            ),
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        const { associatedDiscordAccount } = this.props;
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

        return [
            <section className="col-md-24 p-0 mb-4" key="dpsParsesList">
                <h2 className="form-title col-md-24">
                    Parses for <i>{character.name}</i> Pending Approval
                </h2>
                {discordLinkWarning}
                <ul className="ne-corner">{actionListRendered}</ul>
                {parsesRendered}
            </section>,
        ];
    };

    renderNoDpsParsesCreateOneNotification = () => {
        const { dispatch, notifications } = this.props;
        const character = this.getCharacter();
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

    render = () => {
        const { me } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const character = this.getCharacter();
        if (!character) {
            return <Redirect to="/characters" />;
        }
        this.renderNoDpsParsesCreateOneNotification();
        const dpsParses = character.dps_parses;

        return [...this.renderList(dpsParses, character), <Notification key="notifications" />];
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
