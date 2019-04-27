import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';
import postMyCharacterAction from '../../actions/post-my-character';
import putMyCharacterAction from '../../actions/put-my-character';
import { characters, user } from '../../vendor/data';
import Notification from '../Notification';

class CharacterForm extends PureComponent {
    classOptions = [
        { value: 1, label: 'Dragonknight' },
        { value: 2, label: 'Nightblade' },
        { value: 3, label: 'Sorcerer' },
        { value: 4, label: 'Templar' },
        { value: 5, label: 'Warden' },
        { value: 6, label: 'Necromancer' },
    ];

    roleOptions = [{ value: 1, label: 'Tank' }, { value: 2, label: 'Healer' }, { value: 3, label: 'Damage Dealer (Magicka)' }, { value: 4, label: 'Damage Dealer (Stamina)' }];

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    componentWillUpdate = nextProps => {
        // We had a change in Characters data: Redirect!
        if (nextProps.myCharacters.length !== this.props.myCharacters.length) {
            return this.props.history.push('/characters');
        }
        const { match } = this.props;
        if (match.params && match.params.id) {
            if (this.props.myCharacters !== nextProps.myCharacters) {
                return this.props.history.push('/characters');
            }
        }
    };

    getCharacter = () => {
        const { match, myCharacters } = this.props;
        if (match.params && match.params.id) {
            const characterId = match.params.id;

            return myCharacters.find(item => item.id === parseInt(characterId));
        }

        return undefined;
    };

    handleSubmit = event => {
        event.preventDefault();
        const { match, postMyCharacterAction, putMyCharacterAction } = this.props;
        const data = new FormData(event.target);
        if (match.params && match.params.id) {
            const characterId = match.params.id;

            return putMyCharacterAction(characterId, data);
        }

        return postMyCharacterAction(data);
    };

    renderForm = character => {
        const { match, sets } = this.props;
        const setsOptions = Object.values(sets).map(item => ({ value: item.id, label: item.name }));
        const charactersSetsIds = character ? Object.values(character.sets).map(item => item.id) : [];
        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="characterCreationForm">
                <h2 className="form-title col-md-24">{match.params.id ? 'Edit' : 'Create'} Character</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-6">
                    <label htmlFor="characterName">Character Name:</label>
                    <input
                        type="text"
                        name="name"
                        id="characterName"
                        className="form-control form-control-md"
                        placeholder="Enter..."
                        defaultValue={character ? character.name : ''}
                        autoComplete="off"
                        disabled={!!(character && character.last_submitted_dps_amount)}
                        required
                    />
                </fieldset>
                <fieldset className="form-group col-md-6">
                    <label>Class:</label>
                    <Select
                        options={this.classOptions}
                        defaultValue={character ? this.classOptions.filter(option => option.label === character.class) : this.classOptions[0]}
                        components={Animated}
                        isDisabled={!!(character && character.last_submitted_dps_amount)}
                        name="class"
                    />
                </fieldset>
                <fieldset className="form-group col-md-6">
                    <label>Role:</label>
                    <Select
                        options={this.roleOptions}
                        defaultValue={character ? this.roleOptions.filter(option => option.label === character.role) : this.roleOptions[0]}
                        components={Animated}
                        isDisabled={!!(character && character.last_submitted_dps_amount)}
                        name="role"
                    />
                </fieldset>
                <fieldset className="form-group col-md-6">
                    <label>Full Sets Character Has:</label>
                    <Select
                        options={setsOptions}
                        defaultValue={character ? setsOptions.filter(option => charactersSetsIds.includes(option.value)) : charactersSetsIds}
                        placeholder="Full sets you have..."
                        components={Animated}
                        name="sets[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <Link to="/characters" className="btn btn-info btn-lg mr-1">
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
        const { me } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const character = this.getCharacter();

        return [this.renderForm(character), <Notification key="notifications" />];
    };
}

CharacterForm.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    sets: PropTypes.array,
    myCharacters: characters,
    notifications: PropTypes.array,

    postMyCharacterAction: PropTypes.func.isRequired,
    putMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    sets: state.getIn(['sets']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postMyCharacterAction: data => dispatch(postMyCharacterAction(data)),
    putMyCharacterAction: (characterId, data) => dispatch(putMyCharacterAction(characterId, data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CharacterForm);
