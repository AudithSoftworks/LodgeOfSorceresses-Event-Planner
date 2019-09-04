import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import Animated from 'react-select/animated';
import postMyCharacterAction from '../../actions/post-my-character';
import putMyCharacterAction from '../../actions/put-my-character';
import { characters, content, sets, skills, user } from '../../vendor/data';
import Notification from '../../Components/Notification';

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

    UNSAFE_componentWillUpdate = nextProps => {
        // We had a change in Characters data: Redirect!
        if (nextProps.myCharacters.length !== this.props.myCharacters.length) {
            return this.props.history.push('/@me/characters');
        }
        const { match } = this.props;
        if (match.params && match.params.id) {
            if (this.props.myCharacters !== nextProps.myCharacters) {
                return this.props.history.push('/@me/characters');
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

    parseSkillOptions = skillIds => {
        const { skills } = this.props;

        return skills
            .filter(item => skillIds.includes(item.id))
            .map(item => ({ value: item.id, label: item.name }))
            .sort((a, b) => {
                const nameA = a.label;
                const nameB = b.label;
                if (nameA < nameB) {
                    return -1;
                }
                if (nameA > nameB) {
                    return 1;
                }

                return 0;
            });
    };

    renderForm = character => {
        const { match, sets, content } = this.props;

        const setsOptions = Object.values(sets).map(item => ({ value: item.id, label: item.name }));

        // const tankSkillIds = [
        //     362, // Destruction Staff, Force Shock > Crushing Shock
        //     366, // Destruction Staff, Wall of Elements > Elemental Blockade
        //     372, // Destruction Staff, Weakness to Elements > Elemental Drain
        //     409, // One Handed, Puncture > Pierce Armor
        //     412, // One Handed, Low Slash > Heroic Slash
        //     415, // One Handed, Defensive Posture > Absorb Magic
        //     416, // One Handed, Shield Charge
        //     539, // Vampire, Drain Essence > Invigorating Drain
        //     541, // Vampire, Mist Form
        //     596, // Mages Guild, Balance
        //     617, // Psijic, Meditate
        //     625, // Undaunted, Blood Altar
        //     632, // Undaunted, Inner Fire > Inner Rage
        //     639, // Undaunted, Necrotic Orb > Energy Orb
        //     643, // Alliance War, Assault, Ultimate: War Horn > Aggressive Horn
        //     648, // Alliance War, Assault, Vigor
        //     661, // Alliance War, Support, Barrier > Replenishing Barrier
        //     667, // Alliance War, Support, Purge > Efficient Purge
        // ];
        const supportSkillIds = [
            372, // Destruction Staff, Weakness to Elements > Elemental Drain
            443, // Siphon Spirit
            444, // Quick Siphon
            626, // Undaunted, Blood Altar > Sanguine Altar
            627, // Undaunted, Blood Altar > Overflowing Altar
            636, // Bone Surge
            638, // Undaunted, Necrotic Orb > Mystic Orb
            639, // Undaunted, Necrotic Orb > Energy Orb
            643, // Alliance War, Assault, Ultimate: War Horn > Aggressive Horn
            667, // Alliance War, Support, Purge > Efficient Purge
            668, // Alliance War, Support, Purge > Cleanse
        ];
        const skillsOptions = this.parseSkillOptions(supportSkillIds);
        const contentOptions = Object.values(content).map(item => ({ value: item.id, label: item.version ? item.short_name + ' ' + item.version : item.short_name }));
        const charactersSetsIds = character ? Object.values(character.sets).map(item => item.id) : [];
        const charactersSkillsIds = character ? Object.values(character.skills).map(item => item.id) : [];
        const charactersContentIds = character ? Object.values(character.content).map(item => item.id) : [];
        const heading = (match.params.id ? 'Edit' : 'Create') + ' Character';

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="characterCreationForm">
                <h2 className="form-title col-md-24" title={heading}>
                    {heading}
                </h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-8 col-xl-4">
                    <label htmlFor="characterName">Character Name:</label>
                    <input
                        type="text"
                        name="name"
                        id="characterName"
                        className="form-control form-control-sm"
                        placeholder="Enter..."
                        defaultValue={character ? character.name : ''}
                        autoComplete="off"
                        disabled={!!(character && character.last_submitted_dps_amount)}
                        required
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-xl-4">
                    <label>Class:</label>
                    <Select
                        options={this.classOptions}
                        defaultValue={character ? this.classOptions.filter(option => option.label === character.class) : this.classOptions[0]}
                        components={Animated}
                        isDisabled={!!(character && character.last_submitted_dps_amount)}
                        name="class"
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-xl-6">
                    <label>Role:</label>
                    <Select
                        options={this.roleOptions}
                        defaultValue={character ? this.roleOptions.filter(option => option.label === character.role) : this.roleOptions[0]}
                        components={Animated}
                        isDisabled={!!(character && character.last_submitted_dps_amount)}
                        name="role"
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 col-xl-10">
                    <label>Content Cleared</label>
                    <Select
                        options={contentOptions}
                        defaultValue={character ? contentOptions.filter(option => charactersContentIds.includes(option.value)) : charactersContentIds}
                        placeholder="Content which you actively progressed and cleared (no carries)..."
                        components={Animated}
                        name="content[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-10 col-xl-12">
                    <label>Full Sets Character Has:</label>
                    <Select
                        options={setsOptions}
                        defaultValue={character ? setsOptions.filter(option => charactersSetsIds.includes(option.value)) : charactersSetsIds}
                        placeholder="List full sets only (2/2 or 5/5 etc)..."
                        components={Animated}
                        name="sets[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-14 col-xl-12">
                    <label>Support Skills Unlocked</label>
                    <Select
                        options={skillsOptions}
                        defaultValue={character ? skillsOptions.filter(option => charactersSkillsIds.includes(option.value)) : charactersSkillsIds}
                        placeholder="All support skills you've unlocked and fully leveled..."
                        components={Animated}
                        name="skills[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <Link to="/@me/characters" className="btn btn-info btn-lg mr-1">
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
    sets,
    skills,
    content,
    myCharacters: characters,
    notifications: PropTypes.array,

    postMyCharacterAction: PropTypes.func.isRequired,
    putMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    sets: state.getIn(['sets']),
    skills: state.getIn(['skills']),
    content: state.getIn(['content']),
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
