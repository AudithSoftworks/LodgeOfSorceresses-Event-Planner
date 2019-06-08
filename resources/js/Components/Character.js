import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faAmbulance,
    faBowArrow,
    faShieldAlt,
    faSpinner,
    faSunrise,
    faSunset,
    faSwords,
    faTachometerAlt,
    faTachometerAltAverage,
    faTachometerAltFast,
    faTachometerAltFastest,
    faTachometerAltSlow,
    faTachometerAltSlowest,
    faUser,
    faUsers,
} from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import { errorsAction, successAction } from '../actions/notifications';
import Notification from '../Components/Notification';
import List from "../SubComponents/DpsParses/List";
import { getCharacter } from "../vendor/api";
import { updateCharacter } from "../vendor/api/admin";
import axios from "../vendor/axios";
import { user } from '../vendor/data';
import Loading from './Loading';

library.add(
    faAmbulance,
    faBowArrow,
    faShieldAlt,
    faSpinner,
    faSunrise,
    faSunset,
    faSwords,
    faTachometerAlt,
    faTachometerAltAverage,
    faTachometerAltFast,
    faTachometerAltFastest,
    faTachometerAltSlow,
    faTachometerAltSlowest,
    faUser,
    faUsers
);

class Character extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            character: null,
        };
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    componentDidMount = () => {
        const { me, match } = this.props;
        if (me && match.params && match.params.id) {
            const characterId = match.params.id;
            this.cancelTokenSource = axios.CancelToken.source();
            getCharacter(this.cancelTokenSource, characterId)
                .then(characters => {
                    this.cancelTokenSource = null;
                    const character = characters.entities.characters[characterId];
                    this.setState({ character })
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    handleRerank = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to **Rerank** this Character?')) {
            this.cancelTokenSource = axios.CancelToken.source();
            const currentTarget = event.currentTarget;
            const characterId = parseInt(currentTarget.getAttribute('data-id'));
            const action = currentTarget.getAttribute('data-action');
            const { allCharacters } = this.state;
            updateCharacter(this.cancelTokenSource, characterId, { action })
                .then(response => {
                    if (response.status === 200) {
                        const message = response.data.message;
                        getCharacter(this.cancelTokenSource, characterId)
                            .then(response => {
                                delete (allCharacters.entities.characters[characterId]);
                                allCharacters.entities.characters[response.result] = response.entities.characters[response.result];
                                this.setState({ allCharacters });
                                this.props.dispatch(successAction(message));
                            });
                    }
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    renderDpsParses = character => {
        return character.dps_parses.length
            ? (
                <article className='col-lg-24 mt-5'>
                    <h3>Latest 10 DPS Parses Approved</h3>
                    <List character={character} dpsParses={character.dps_parses.slice(0, 10)} />
                </article>
            )
            : null;
    };

    renderCharacter = character => {
        const actionList = {
            promote:
                character['role'].indexOf('Damage Dealer') === -1 ? (
                    <a href='#' onClick={this.handleRerank} data-id={character.id} data-action='promote' title="Promote Character">
                        <FontAwesomeIcon icon={['far', 'sunrise']} />
                    </a>
                ) : null,
            demote:
                character['role'].indexOf('Damage Dealer') === -1 ? (
                    <a href='#' onClick={this.handleRerank} data-id={character.id} data-action='demote' title="Demote Character">
                        <FontAwesomeIcon icon={['far', 'sunset']} />
                    </a>
                ) : null,
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            if (link) {
                actionListRendered.push(<li key={actionType}>{link}</li>);
            }
        }

        const characterContent = character.content
            .map(content => ({ id: content.id, name: content.name.concat(' ', content.version || '') }))
            .reduce((acc, curr) => [acc, ' ', <li key={curr.id}>{curr.name}</li>], '');
        const characterSets = character.sets
            .map(set => (
                <a key={set['id']} href={'https://eso-sets.com/set/' + set['slug']} className="badge badge-dark" target='_blank'>
                    {set['name']}
                </a>
            ))
            .reduce((acc, curr) => [acc, ' ', <li key={curr.key}>{curr}</li>], '');
        const characterSkills = character.skills
            .map(skill => (
                <a key={skill['id']} href={'https://eso-skillbook.com/skill/' + skill['slug']} className="badge badge-dark" target='_blank'>
                    {skill['name']}
                </a>
            ))
            .reduce((acc, curr) => [acc, ' ', <li key={curr.key}>{curr}</li>], '');

        return [
            <section className="col-md-24 p-0 mb-4 d-flex flex-wrap" key="character">
                <h2 className="form-title col-md-24">{character.name}</h2>
                <ul className="ne-corner">{actionListRendered}</ul>
                <dl className='col-lg-8'>
                    <dt>Class</dt>
                    <dd>{character.class}</dd>

                    <dt>Role</dt>
                    <dd>{character.role}</dd>
                </dl>
                <article className='col-lg-6'>
                    <h3>Content Cleared</h3>
                    {characterContent.length ? <ul>{characterContent}</ul> : 'None'}
                </article>
                <article className='col-lg-5'>
                    <h3>Sets Acquired</h3>
                    {characterSets.length ? <ul>{characterSets}</ul> : 'None'}
                </article>
                <article className='col-lg-5'>
                    <h3>Skills Leveled</h3>
                    {characterSkills.length ? <ul>{characterSkills}</ul> : 'None'}
                </article>
                {this.renderDpsParses(character)}
            </section>,
        ];
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        const { character } = this.state;
        if (!character) {
            return [
                <Loading message="Fetching Character..." key="loading" />,
                <Notification key="notifications" />
            ];

        }

        return [...this.renderCharacter(character), <Notification key="notifications" />];
    };
}

Character.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Character);
