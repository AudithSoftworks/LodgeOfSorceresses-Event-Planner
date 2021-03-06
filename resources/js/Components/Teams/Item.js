import { faChevronCircleLeft, faSunrise, faSunset, faTrashAlt } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import deleteTeamsCharactersAction from '../../actions/delete-teams-characters';
import { errorsAction } from '../../actions/notifications';
import postTeamsCharactersAction from '../../actions/post-teams-characters';
import { renderActionList } from '../../helpers';
import { getAllCharacters } from '../../vendor/api';
import axios from '../../vendor/axios';
import { team, teams, user } from '../../vendor/data';
import Loading from '../Loading';
import Notification from '../Notification';
import List from '../TeamsCharacters/List';

class Item extends Component {
    constructor(props) {
        super(props);
        this.state = {
            characters: null,
            selectedCharacters: null,
            team: props.team,
        };
    }

    getAllCharacters = tier => {
        this.cancelTokenSource = axios.CancelToken.source();

        return getAllCharacters(this.cancelTokenSource, tier).catch(error => {
            if (!axios.isCancel(error)) {
                const message = error.response.data.message || error.response.statusText || error.message;
                this.props.dispatch(errorsAction(message));
            }
        });
    };

    componentDidMount = () => {
        const { authorizedTeamManager } = this.props;
        const { characters, team } = this.state;
        if (authorizedTeamManager && !characters) {
            this.getAllCharacters(team.tier).then(characters => {
                this.cancelTokenSource = null;
                this.setState({ characters });
            });
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    handleTeamsCharactersPost = event => {
        event.preventDefault();
        const data = new FormData(event.target);
        const { team } = this.state;

        return this.props.postTeamsCharactersAction(team.id, data).then(() => {
            this.getAllCharacters(team.tier).then(characters => {
                this.cancelTokenSource = null;
                this.setState({
                    characters,
                    selectedCharacters: null,
                    team: this.props.teams.find(t => t.id === team.id),
                });
            });
        });
    };

    handleTeamsCharactersDelete = event => {
        event.preventDefault();
        if (confirm('Are you sure you want to remove this character from the team?')) {
            const currentTarget = event.currentTarget;
            const characterId = parseInt(currentTarget.getAttribute('data-id'));
            const { team } = this.state;

            return this.props.deleteTeamsCharactersAction(team.id, characterId).then(() => {
                this.getAllCharacters(team.tier).then(characters => {
                    this.cancelTokenSource = null;
                    this.setState({
                        characters,
                        team: this.props.teams.find(t => t.id === team.id),
                    });
                });
            });
        }
    };

    handleSelectChange = event => {
        this.setState({
            selectedCharacters: event,
        });
    };

    renderAddMemberForm = characters => {
        const { team, selectedCharacters } = this.state;
        const teamMembersIds = team.members.reduce((acc, item) => {
            acc.push(item.id);

            return acc;
        }, []);
        const characterOptions = Object.values(characters.entities['characters'])
            .filter(c => teamMembersIds.indexOf(c.id) === -1 && c.owner !== null)
            .map(c => ({
                value: c.id,
                label: '@' + c.owner.name + ': ' + c.name + ' (' + c.class + '/' + c.role + ') [Tier-' + c.approved_for_tier + ']',
            }));

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleTeamsCharactersPost} key="teams-characters-store-form">
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-24">
                    <label>Eligible Characters</label>
                    <Select
                        options={characterOptions}
                        value={selectedCharacters}
                        onChange={this.handleSelectChange}
                        placeholder="Select the characters to be invited to the team..."
                        components={makeAnimated()}
                        name="characterIds[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <button className="btn btn-primary btn-lg" type="submit">
                        Invite To Team
                    </button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { authorizedTeamManager, changeTierHandler, deleteTeamHandler, me } = this.props;
        const { characters } = this.state;
        if (authorizedTeamManager && !characters) {
            return [<Loading message="Fetching eligible character list..." key="loading" />, <Notification key="notifications" />];
        }

        const { team } = this.state;
        const actionList = {
            return: (
                <Link to={'/teams'} title="Back to Teams">
                    <FontAwesomeIcon icon={faChevronCircleLeft} />
                </Link>
            ),
            tierIncrease:
                typeof changeTierHandler === 'function' && authorizedTeamManager && team.tier < 4 ? (
                    <a href="#" onClick={changeTierHandler} data-id={team.id} data-action="increase-tier" title="Increase Tier">
                        <FontAwesomeIcon icon={faSunrise} />
                    </a>
                ) : null,
            tierDecrease:
                typeof changeTierHandler === 'function' && authorizedTeamManager && team.tier > 1 ? (
                    <a href="#" onClick={changeTierHandler} data-id={team.id} data-action="decrease-tier" title="Decrease Tier">
                        <FontAwesomeIcon icon={faSunset} />
                    </a>
                ) : null,
            delete:
                typeof deleteTeamHandler === 'function' && authorizedTeamManager ? (
                    <Link to="#" onClick={deleteTeamHandler} data-id={team.id} title="Delete Team">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        return [
            <section className="col-md-24 p-0 mb-4 d-flex flex-wrap" key="character">
                <h2 className="form-title col-md-24">{team.name}</h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <dl className="col-lg-4">
                    <dt>Tier</dt>
                    <dd>{team.tier}</dd>

                    <dt>Leader</dt>
                    <dd>{'@' + team.led_by.name}</dd>

                    <dt># of Members</dt>
                    <dd>{team.members.length}</dd>
                </dl>
                <article className="col-lg-20">{authorizedTeamManager ? this.renderAddMemberForm(characters) : null}</article>
                <List authorizedTeamManager={authorizedTeamManager} deleteTeamMembershipHandler={this.handleTeamsCharactersDelete} me={me} team={team} />
            </section>,
        ];
    };
}

Item.propTypes = {
    authorizedTeamManager: PropTypes.bool.isRequired,
    changeTierHandler: PropTypes.func, // based on existense of this param, we render ChangeTier buttons
    deleteTeamHandler: PropTypes.func, // based on existense of this param, we render Delete button
    axiosCancelTokenSource: PropTypes.object,
    me: user.isRequired,
    team,
    teams,

    dispatch: PropTypes.func.isRequired,
    postTeamsCharactersAction: PropTypes.func.isRequired,
    deleteTeamsCharactersAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postTeamsCharactersAction: (teamId, data) => dispatch(postTeamsCharactersAction(teamId, data)),
    deleteTeamsCharactersAction: (teamId, characterId) => dispatch(deleteTeamsCharactersAction(teamId, characterId)),
});

export default connect(mapStateToProps, mapDispatchToProps)(Item);
