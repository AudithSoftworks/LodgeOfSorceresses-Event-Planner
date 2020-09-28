import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import deleteMyCharacterAction from '../actions/delete-my-character';
import putCharacterAction from '../actions/put-character';
import viewCharacterAction from '../actions/view-character';
import Character from '../Components/Characters/Item';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { authorizeUser, deleteMyCharacter, rerankCharacter } from '../helpers';
import { character, user } from '../vendor/data';

class Characters extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
        this.deleteMyCharacter = deleteMyCharacter.bind(this);
        this.rerankHandler = rerankCharacter.bind(this);
    }

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    componentDidMount = () => {
        const { character, me, match } = this.props;
        const characterIdParam = me && match.params.id ? parseInt(match.params.id) : null;
        if (characterIdParam && (!Object.keys(character).length || character.id !== characterIdParam)) {
            this.props.viewCharacterAction(characterIdParam);
        }
    };

    render = () => {
        const { me, character, location, match } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        if (!this.authorizeUser(true) || !match.params.id) {
            return <Redirect to="/" />;
        }

        if (character !== null && (!Object.keys(character).length || character.id !== parseInt(match.params.id))) {
            return [<Loading message="Fetching Character information..." key="loading" />, <Notification key="notifications" />];
        } else if (character === null) {
            return <Redirect to="/@me/characters" />;
        }

        return [
            <Character
                character={character}
                rerankHandler={me && me.isAdmin ? this.rerankHandler : null}
                deleteHandler={me.id === character.owner.id ? this.deleteMyCharacter : null}
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
    character,
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
    viewCharacterAction: PropTypes.func.isRequired,
    putCharacterAction: PropTypes.func.isRequired,
    deleteMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
    me: state.getIn(['me']),
    character: state.getIn(['selectedCharacter']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    viewCharacterAction: characterId => dispatch(viewCharacterAction(characterId)),
    putCharacterAction: (characterId, data) => dispatch(putCharacterAction(characterId, data)),
    deleteMyCharacterAction: characterId => dispatch(deleteMyCharacterAction(characterId)),
});

export default connect(mapStateToProps, mapDispatchToProps)(Characters);
