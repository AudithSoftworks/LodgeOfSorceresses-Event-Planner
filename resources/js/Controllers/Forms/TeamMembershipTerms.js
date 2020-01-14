import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import deleteTeamsCharactersAction from "../../actions/delete-teams-characters";
import putTeamsCharactersAction from "../../actions/put-teams-characters";
import Loading from "../../Components/Loading";
import Notification from '../../Components/Notification';
import { teams, user } from "../../vendor/data";

class TeamMembershipTerms extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            team: null,
            character: null,
        };
    }

    componentDidMount = () => {
        const { me, history, match, teams } = this.props;
        if (teams) {
            const { character, team } = this.state;
            if (!match.params.id || !match.params['cId']) {
                history.push('/teams');
            }

            if (!team || !character) {
                const selectedTeam = teams.find(item => item.id === parseInt(match.params.id));
                if (!selectedTeam) {
                    history.push('/teams');
                }

                const character = selectedTeam.members.find(c => c.owner.id === me.id);
                if (!character || character.id !== parseInt(match.params['cId'])) {
                    history.push('/teams');
                }
                this.setState({ team: selectedTeam, character });
            }
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    handlePutTeamsCharacters = event => {
        event.preventDefault();
        const { history, putTeamsCharactersAction } = this.props;
        const { team, character } = this.state;
        const data = new FormData(event.target);

        return putTeamsCharactersAction(team.id, character.id, data)
            .then(() => {
                history.push('/teams/' + team.id);
            });
    };

    handleDeleteTeamsCharacters = event => {
        event.preventDefault();
        if (confirm("Are you sure you want to **leave** this team?")) {
            const { deleteTeamsCharactersAction, history } = this.props;
            const { team, character } = this.state;

            return deleteTeamsCharactersAction(team.id, character.id)
                .then(() => {
                    history.push('/teams/' + team.id);
                });
        }
    };

    render = () => {
        const { location, me, teams } = this.props;
        if (!me || !teams) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }
        const { team, character } = this.state;
        if (!team || !character) {
            return [<Loading message="Fetching team membership records..." key="loading" />, <Notification key="notifications" />];
        }

        return [
            <article key="tos" className="col-xs-24 p-0">
                <form onSubmit={this.handlePutTeamsCharacters} className="col-xs-24 p-0">
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                    <fieldset className="form-group col-24 mt-5 pt-5 pb-5 text-center">
                        <input hidden="checkbox" name="accepted_terms" value="1" readOnly={true} />
                        <p>I have read <a href='https://lodgeofsorceresses.com/topic/4887-pve-raid-core-requirements-to-join/' target='_blank'>Requirements to Join Endgame Guidelines</a>.</p>
                        <p>I have read & understood <a href='https://lodgeofsorceresses.com/topic/5506-endgame-attendance-guidelines/' target='_blank'>Endgame Attendance Guidelines</a>.</p>
                        <p>I have done what is described in <em>Requirements</em> section of <em>Endgame Attendance Guidelines</em> - installed Addons, configured the game accordingly etc.</p>
                        <p>By joining, I accept the terms stated in <em>Endgame Attendance Guidelines</em>.</p>
                    </fieldset>
                    <fieldset className="form-group col-24 text-center">
                        <button className="btn btn-success btn-lg mb-4 ml-auto mr-auto d-block" type="submit">
                            Accept Invitation & Join
                        </button>
                        <button className="btn btn-danger btn-lg ml-auto mr-auto d-block" type="button" onClick={this.handleDeleteTeamsCharacters}>
                            Reject Invitation
                        </button>
                    </fieldset>
                </form>
            </article>,
            <Notification key="notifications" />
        ];
    };
}

TeamMembershipTerms.propTypes = {
    axiosCancelTokenSource: PropTypes.object,
    history: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    match: PropTypes.object.isRequired,
    me: user,
    notifications: PropTypes.array,
    teams: teams,

    putTeamsCharactersAction: PropTypes.func.isRequired,
    deleteTeamsCharactersAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(["me"]),
    teams: state.getIn(["teams"]),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    putTeamsCharactersAction: (teamId, characterId, data) => dispatch(putTeamsCharactersAction(teamId, characterId, data)),
    deleteTeamsCharactersAction: (teamId, characterId, data) => dispatch(deleteTeamsCharactersAction(teamId, characterId, data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(TeamMembershipTerms);
