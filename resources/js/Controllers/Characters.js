import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { connect } from "react-redux";
import { Redirect } from "react-router-dom";
import deleteMyCharacterAction from "../actions/delete-my-character";
import { errorsAction, successAction } from "../actions/notifications";
import Character from "../Components/Characters/Item";
import Loading from "../Components/Loading";
import Notification from "../Components/Notification";
import { authorizeAdmin, authorizeUser, deleteMyCharacter } from "../helpers";
import { getCharacter } from "../vendor/api";
import { updateCharacter } from "../vendor/api/admin";
import axios from "../vendor/axios";
import { user } from "../vendor/data";

class Characters extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            selectedCharacter: null,
        };
        this.deleteMyCharacter = deleteMyCharacter.bind(this);
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel("Unmount");
    };

    componentDidMount = () => {
        const { me, match } = this.props;
        if (me && match.params.id) {
            this.cancelTokenSource = axios.CancelToken.source();
            const characterId = match.params.id;
            getCharacter(this.cancelTokenSource, characterId)
                .then(character => {
                    this.cancelTokenSource = null;
                    this.setState({ selectedCharacter: character.entities.characters[characterId] });
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    rerankHandler = event => {
        event.preventDefault();
        if (confirm("Are you sure you want to **Rerank** this Character?")) {
            this.cancelTokenSource = axios.CancelToken.source();
            const currentTarget = event.currentTarget;
            const characterId = parseInt(currentTarget.getAttribute("data-id"));
            const action = currentTarget.getAttribute("data-action");
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

    render = () => {
        const { me, groups, location, match } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }

        if (me && groups) {
            if (!authorizeUser(this.props, true) || !match.params.id) {
                return <Redirect to="/" />;
            }
        }

        const { selectedCharacter } = this.state;
        if (!selectedCharacter) {
            return [<Loading message="Fetching Character information..." key="loading" />, <Notification key="notifications" />];
        }

        return [
            <Character
                character={selectedCharacter}
                rerankHandler={authorizeAdmin(this.props) ? this.rerankHandler : null}
                deleteHandler={me.id === selectedCharacter.owner.id ? this.deleteMyCharacter : null}
                key="character"
            />,
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
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(["me"]),
    groups: state.getIn(["groups"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteMyCharacterAction: characterId => dispatch(deleteMyCharacterAction(characterId)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Characters);
