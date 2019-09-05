import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "characters-scss" */
    '../../../sass/_characters.scss'
);

import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faAmbulance,
    faBowArrow,
    faPortrait,
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
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import { errorsAction, infosAction, successAction } from '../../actions/notifications';
import { authorizeAdmin } from '../../helpers';
import Notification from '../../Components/Notification';
import { getAllCharacters, getCharacter } from '../../vendor/api';
import { updateCharacter } from '../../vendor/api/admin';
import axios from '../../vendor/axios';
import { characters, user } from '../../vendor/data';
import Loading from '../../Components/Loading';

library.add(
    faAmbulance,
    faBowArrow,
    faPortrait,
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

class Characters extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            filters: {
                no_clearance: false,
                tier_1: false,
                tier_2: true,
                tier_3: true,
                tier_4: true,
                role_1: false,
                role_2: false,
                role_3: true,
                role_4: true,
            },
            allCharacters: null,
        };
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    componentDidMount = () => {
        const { me } = this.props;
        if (me) {
            this.cancelTokenSource = axios.CancelToken.source();
            getAllCharacters(this.cancelTokenSource)
                .then(allCharacters => {
                    this.cancelTokenSource = null;
                    this.setState({ allCharacters });
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
                        getCharacter(this.cancelTokenSource, characterId).then(response => {
                            delete allCharacters.entities.characters[characterId];
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

    filter = (event, typeUpdating) => {
        const temp = Object.assign({}, this.state.filters);
        for (const [type, value] of Object.entries(temp)) {
            if (type === typeUpdating) {
                temp[type] = !value;
                event.currentTarget.classList.toggle('inactive');
            } else {
                temp[type] = value;
            }
        }
        this.setState({
            filters: temp,
        });
    };

    renderListItem = character => {
        if (character.role === 'Tank' && !this.state.filters.role_1) return null;
        if (character.role === 'Healer' && !this.state.filters.role_2) return null;
        if (character.role === 'Magicka DD' && !this.state.filters.role_3) return null;
        if (character.role === 'Stamina DD' && !this.state.filters.role_4) return null;
        if (character['approved_for_t4'] && !this.state.filters.tier_4) return null;
        if (!character['approved_for_t4'] && character['approved_for_t3'] && !this.state.filters.tier_3) return null;
        if (!character['approved_for_t4'] && !character['approved_for_t3'] && character['approved_for_t2'] && !this.state.filters.tier_2) return null;
        if (
            !character['approved_for_t4'] &&
            !character['approved_for_t3'] &&
            !character['approved_for_t2'] &&
            character['approved_for_t1'] &&
            !this.state.filters.tier_1
        ) {
            return null;
        }
        if (
            !character['approved_for_t4'] &&
            !character['approved_for_t3'] &&
            !character['approved_for_t2'] &&
            !character['approved_for_t1'] &&
            !this.state.filters.no_clearance
        ) {
            return null;
        }
        const characterSets = character.sets.map(set => (
            <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className="badge badge-dark">
                {set['name']}
            </a>
        ));
        character.actionList = {
            promote:
                character['role'].indexOf('DD') === -1 ? (
                    <a href="#" onClick={this.handleRerank} data-id={character.id} data-action="promote" title="Promote Character">
                        <FontAwesomeIcon icon={['far', 'sunrise']} />
                    </a>
                ) : null,
            demote:
                character['role'].indexOf('DD') === -1 ? (
                    <a href="#" onClick={this.handleRerank} data-id={character.id} data-action="demote" title="Demote Character">
                        <FontAwesomeIcon icon={['far', 'sunset']} />
                    </a>
                ) : null,
            view: (
                <Link to={'/characters/' + character.id} title="Character Sheet">
                    <FontAwesomeIcon icon={['far', 'portrait']} />
                </Link>
            ),
        };
        const actionListRendered = [];
        for (const [actionType, link] of Object.entries(character.actionList)) {
            if (link) {
                actionListRendered.push(<li key={actionType}>{link}</li>);
            }
        }

        let rowBgColor = 'no_clearance';
        if (character['approved_for_t4']) {
            rowBgColor = 'tier_4';
        } else if (character['approved_for_t3']) {
            rowBgColor = 'tier_3';
        } else if (character['approved_for_t2']) {
            rowBgColor = 'tier_2';
        } else if (character['approved_for_t1']) {
            rowBgColor = 'tier_1';
        }

        return (
            <tr className={rowBgColor} key={'characterRow-' + character.id} data-id={character.id}>
                <td>{character.owner.name}</td>
                <td>
                    {character.name}
                    <br />
                    <small>
                        {character.class} / {character.role} - DPS: {character.last_submitted_dps_amount || 'N/A'}
                    </small>
                </td>
                <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                <td>
                    <ul className="actionList">{actionListRendered}</ul>
                </td>
            </tr>
        );
    };

    renderList = allCharacters => {
        let charactersRendered = allCharacters.result.map(characterId => {
            const character = allCharacters.entities['characters'][characterId];
            return this.renderListItem(character);
        });
        if (charactersRendered.length) {
            charactersRendered = [
                <table key="character-list-table" className="character-list-table pl-2 pr-2 col-md-24">
                    <thead>
                        <tr>
                            <th scope="col">Player Name</th>
                            <th scope="col">Character Name</th>
                            <th scope="col">Sets</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>{charactersRendered}</tbody>
                </table>,
            ];
        }

        const { filters } = this.state;
        const filterList = {
            no_clearance: (
                <button type="button" onClick={event => this.filter(event, 'no_clearance')} className={'ne-corner ' + (filters.no_clearance || 'inactive')} title="Filter No-Clearance">
                    <FontAwesomeIcon icon={['far', 'tachometer-alt-slowest']} />
                </button>
            ),
            tier_1: (
                <button type="button" onClick={event => this.filter(event, 'tier_1')} className={'ne-corner tier_1 ' + (filters.tier_1 || 'inactive')} title="Filter Midgame-cleared">
                    <FontAwesomeIcon icon={['far', 'tachometer-alt-slow']} />
                </button>
            ),
            tier_2: (
                <button
                    type="button"
                    onClick={event => this.filter(event, 'tier_2')}
                    className={'ne-corner tier_2 ' + (filters.tier_2 || 'inactive')}
                    title="Filter Endgame Tier-0-cleared"
                >
                    <FontAwesomeIcon icon={['far', 'tachometer-alt-average']} />
                </button>
            ),
            tier_3: (
                <button
                    type="button"
                    onClick={event => this.filter(event, 'tier_3')}
                    className={'ne-corner tier_3 ' + (filters.tier_3 || 'inactive')}
                    title="Filter Endgame Tier-1-cleared"
                >
                    <FontAwesomeIcon icon={['far', 'tachometer-alt-fast']} />
                </button>
            ),
            tier_4: (
                <button
                    type="button"
                    onClick={event => this.filter(event, 'tier_4')}
                    className={'ne-corner tier_4 ' + (filters.tier_4 || 'inactive')}
                    title="Filter Endgame Tier-2-cleared"
                >
                    <FontAwesomeIcon icon={['far', 'tachometer-alt-fastest']} />
                </button>
            ),
            role_1: (
                <button type="button" onClick={event => this.filter(event, 'role_1')} className={'ne-corner ' + (filters.role_1 || 'inactive')} title="Filter Tanks">
                    <FontAwesomeIcon icon={['far', 'shield-alt']} />
                </button>
            ),
            role_2: (
                <button type="button" onClick={event => this.filter(event, 'role_2')} className={'ne-corner ' + (filters.role_2 || 'inactive')} title="Filter Healers">
                    <FontAwesomeIcon icon={['far', 'ambulance']} />
                </button>
            ),
            role_3: (
                <button type="button" onClick={event => this.filter(event, 'role_3')} className={'ne-corner ' + (filters.role_3 || 'inactive')} title="Filter Magicka DDs">
                    <FontAwesomeIcon icon={['far', 'bow-arrow']} />
                </button>
            ),
            role_4: (
                <button type="button" onClick={event => this.filter(event, 'role_4')} className={'ne-corner ' + (filters.role_4 || 'inactive')} title="Filter Stamina DDs">
                    <FontAwesomeIcon icon={['far', 'swords']} />
                </button>
            ),
        };
        const actionListRendered = [];
        for (const [filterType, link] of Object.entries(filterList)) {
            actionListRendered.push(<li key={filterType}>{link}</li>);
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="characterList">
                <h2 className="form-title col-md-24">Character List</h2>
                <article className="alert-info">
                    <b>Usage tips:</b>
                    <ul>
                        <li>Mouse-over the character name for action buttons to reveal to the right of row.</li>
                        <li>
                            Click <FontAwesomeIcon icon={['far', 'tachometer-alt']} /> icon to the right to see that Character's Processed Parses.
                        </li>
                    </ul>
                </article>
                <ul className="ne-corner">{actionListRendered}</ul>
                {charactersRendered}
            </section>,
        ];
    };

    renderNoCharactersFoundNotification = allCharacters => {
        const { dispatch, notifications } = this.props;
        if (allCharacters && !allCharacters.result.length && notifications.find(n => n.key === 'admin-no-characters-found') === undefined) {
            const message = [<Fragment key="f-1">No Characters Found!</Fragment>].reduce((acc, curr) => [acc, ' ', curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                    },
                    'admin-no-characters-found'
                )
            );
        }
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname }}} />;
        }

        if (!authorizeAdmin(this.props)) {
            return history.push('/');
        }

        const { allCharacters } = this.state;
        if (!allCharacters) {
            return [<Loading message="Fetching the list of all Characters..." key="loading" />, <Notification key="notifications" />];
        }
        this.renderNoCharactersFoundNotification(allCharacters);

        return [...this.renderList(allCharacters), <Notification key="notifications" />];
    };
}

Characters.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Characters);
