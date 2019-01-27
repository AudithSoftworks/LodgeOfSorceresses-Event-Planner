import Axios from '../../vendor/Axios';
import React, { Component } from 'react';
import { Link, Redirect } from "react-router-dom";
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';

class CharacterCreateForm extends Component {
    classOptions = [
        {value: 1, label: 'Dragonknight'},
        {value: 2, label: 'Nightblade'},
        {value: 3, label: 'Sorcerer'},
        {value: 4, label: 'Templar'},
        {value: 5, label: 'Warden'},
        {value: 6, label: 'Necromancer'},
    ];

    roleOptions = [
        {value: 1, label: 'Tank'},
        {value: 2, label: 'Healer'},
        {value: 3, label: 'Damage Dealer (Magicka)'},
        {value: 4, label: 'Damage Dealer (Stamina)'},
    ];

    constructor(props) {
        super(props);
        this.state = {
            setsLoaded: false,
            characterCreated: null,
            sets: [],
            error: null
        };
        this.handleSubmit = this.handleSubmit.bind(this);
        this.render = this.render.bind(this);
    };

    handleSubmit = (event) => {
        event.preventDefault();
        const data = new FormData(event.target);
        Axios
            .post('/api/chars', data)
            .then((response) => {
                this.setState({
                    characterCreated: response.data.success === true,
                    error: null
                });
            })
            .catch(function (error) {
                this.setState({
                    characterCreated: false,
                    error: error
                });
            });
    };

    componentDidMount = () => {
        Axios
            .get('/api/sets')
            .then((response) => {
                this.setState({
                    setsLoaded: true,
                    sets: response.data.sets,
                    error: null
                });
            })
            .catch(function (error) {
                this.setState({
                    setsLoaded: true,
                    sets: [],
                    error: error
                });
            });
    };
    renderForm = (sets) => {
        const setsOptions = Object.values(sets).map(
            item => ({value: item.id, label: item.name})
        );

        return (
            <form className='col-md-24 d-flex flex-row flex-wrap p-0' onSubmit={this.handleSubmit} key='characterCreationForm'>
                <h2 className="form-title font-green col-md-24">Create Character</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')}/>
                <fieldset className='form-group col-md-6'>
                    <label htmlFor='characterName'>Character Name:</label>
                    <input
                        type='text'
                        name='name'
                        id='characterName'
                        className='form-control form-control-md'
                        placeholder='Enter...'
                        autoComplete='off'
                        required
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Class:</label>
                    <Select
                        options={this.classOptions}
                        defaultValue={this.classOptions[0]}
                        components={Animated}
                        name='class'
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Role:</label>
                    <Select
                        options={this.roleOptions}
                        defaultValue={this.roleOptions[0]}
                        components={Animated}
                        name='role'
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Supportive Sets:</label>
                    <Select
                        options={setsOptions}
                        placeholder='Full sets you have...'
                        components={Animated}
                        name='sets[]'
                        isMulti
                    />
                </fieldset>
                <fieldset className='form-group col-md-24 text-right'>
                    <Link to="/chars" className="btn btn-info btn-lg mr-1">Cancel</Link>
                    <button className="btn btn-primary btn-lg" type="submit">Save</button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const {setsLoaded, characterCreated, sets, error} = this.state;
        if (error) {
            return <fieldset className='error'>Error: {error}</fieldset>;
        } else if (!setsLoaded) {
            return <fieldset className='general'>Loading...</fieldset>;
        } else if (characterCreated) {
            return <Redirect to="/chars"/>
        }

        return [this.renderForm(sets)];
    };
}

export default CharacterCreateForm;
