import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import Animated from 'react-select/animated';
import { errorsAction } from "../../actions/notifications";
import postTeamAction from "../../actions/post-team";
import putTeamAction from "../../actions/put-team";
import Loading from "../../Components/Loading";
import Notification from '../../Components/Notification';
import { getAllUsers } from "../../vendor/api";
import axios from "../../vendor/axios";
import { teams } from "../../vendor/data";

class TeamForm extends PureComponent {
    tierOptions = [{ value: 1, label: 'Tier-1' }, { value: 2, label: 'Tier-2' }, { value: 3, label: 'Tier-3' }, { value: 4, label: 'Tier-4' }];

    constructor(props) {
        super(props);
        this.state = {
            users: null,
        };
    }

    componentDidMount = () => {
        const { teams } = this.props;
        if (teams) {
            const { users } = this.state;
            if (!users) {
                this.cancelTokenSource = axios.CancelToken.source();
                getAllUsers(this.cancelTokenSource)
                    .then(users => {
                        this.cancelTokenSource = null;
                        this.setState({ users });
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            const message = error.response.data.message || error.response.statusText || error.message;
                            this.props.dispatch(errorsAction(message));
                        }
                    });
            }
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    UNSAFE_componentWillUpdate = nextProps => {
        // We had a change in Teams data: Redirect!
        if (nextProps.teams.length !== this.props.teams.length) {
            return this.props.history.push('/teams');
        }
        const { match } = this.props;
        if (match.params && match.params.id) {
            if (this.props.teams !== nextProps.teams) {
                return this.props.history.push('/teams');
            }
        }
    };

    getTeam = () => {
        const { match, teams } = this.props;
        if (match.params && match.params.id) {
            const teamId = match.params.id;

            return teams.find(item => item.id === parseInt(teamId));
        }

        return undefined;
    };

    handleSubmit = event => {
        event.preventDefault();
        const { match, postTeamAction, putTeamAction } = this.props;
        const data = new FormData(event.target);
        if (match.params && match.params.id) {
            return putTeamAction(match.params.id, data);
        }

        return postTeamAction(data);
    };

    renderForm = team => {
        const { match } = this.props;
        const { users } = this.state;

        const userOptions = Object.values(users.entities['user']).map(item => ({ value: item.id, label: item.name }));
        const heading = (match.params.id ? 'Edit' : 'Create') + ' Team';

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="teamCreationForm">
                <h2 className="form-title col-md-24" title={heading}>
                    {heading}
                </h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-8 col-xl-7">
                    <label htmlFor="characterName">Team Name:</label>
                    <input
                        type="text"
                        name="name"
                        id="teamName"
                        className="form-control form-control-sm"
                        placeholder="Enter..."
                        defaultValue={team ? team.name : ''}
                        autoComplete="off"
                        required
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-xl-3">
                    <label>Content Tier:</label>
                    <Select
                        options={this.tierOptions}
                        defaultValue={team ? this.tierOptions.filter(option => option.value === team.tier) : this.tierOptions[0]}
                        components={Animated}
                        name="tier"
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-xl-8">
                    <label>Discord Role Id:</label>
                    <input
                        type="text"
                        name="discord_id"
                        id="discordId"
                        className="form-control form-control-sm"
                        placeholder="Discord Role Id"
                        defaultValue={team ? team.discord_id : ''}
                        autoComplete="off"
                        required
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 col-xl-6">
                    <label>Team Leader</label>
                    <Select
                        options={userOptions}
                        defaultValue={team ? userOptions.filter(option => team.led_by.id === option.id) : null}
                        placeholder="Team Leader..."
                        components={Animated}
                        name="led_by"
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <Link to="/teams" className="btn btn-info btn-lg mr-1">
                        Cancel
                    </Link>
                    <button className="btn btn-primary btn-lg" type="submit">
                        Save
                    </button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { teams, location } = this.props;
        if (!teams) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }
        const { users } = this.state;
        if (!users) {
            return [<Loading message="Fetching user list..." key="loading" />, <Notification key="notifications" />];
        }

        const team = this.getTeam();

        return [this.renderForm(team), <Notification key="notifications" />];
    };
}

TeamForm.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    teams,
    notifications: PropTypes.array,

    postTeamAction: PropTypes.func.isRequired,
    putTeamAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    teams: state.getIn(["teams"]),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postTeamAction: data => dispatch(postTeamAction(data)),
    putTeamAction: (teamId, data) => dispatch(putTeamAction(teamId, data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(TeamForm);
