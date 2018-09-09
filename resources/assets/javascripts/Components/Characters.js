import Axios from '../vendor/Axios';
import React, { Component } from 'react';
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';

class CharacterCreateForm extends Component {
    constructor(props) {
        super(props);
        this.state = {
            setsLoaded: false,
            sets: [],
            error: null
        };
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit = (event) => {
        event.preventDefault();
        const data = new FormData(event.target);
        Axios
            .post('/api/chars', data)
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

    componentDidMount = () => {
        Axios
            .get('/api/sets')
            .then((response) => {
                // console.log(response.data.sets);
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

    render = () => {
        const {setsLoaded, sets, error} = this.state;
        if (error) {
            return <fieldset className='error'>Error</fieldset>;
        } else if (!setsLoaded) {
            return <fieldset className='general'>Loading</fieldset>;
        } else {
            const classOptions = [
                {value: 1, label: 'Dragonknight'},
                {value: 2, label: 'Nightblade'},
                {value: 3, label: 'Sorcerer'},
                {value: 4, label: 'Templar'},
                {value: 5, label: 'Warden'},
            ];
            const roleOptions = [
                {value: 1, label: 'Tank'},
                {value: 2, label: 'Healer'},
                {value: 3, label: 'Damage Dealer (Magicka)'},
                {value: 4, label: 'Damage Dealer (Stamina)'},
            ];
            const setsOptions = Object.values(sets).map(
                item => ({value: item.id, label: item.name})
            );

            return (
                <form className='col-md-24' onSubmit={this.handleSubmit}>
                    <h2 className="form-title font-green col-md-24">Create Character</h2>
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')}/>
                    <fieldset className='form-group'>
                        <label htmlFor='characterName'>Character Name:</label>
                        <input
                            type='text'
                            name='name'
                            id='characterName'
                            className='form-control form-control-lg'
                            placeholder='Enter...'
                            autoComplete='off'
                            required
                        />
                    </fieldset>
                    <fieldset className='form-group'>
                        <label>Class:</label>
                        <Select
                            options={classOptions}
                            defaultValue={classOptions[0]}
                            components={Animated}
                            name='class'
                        />
                    </fieldset>
                    <fieldset className='form-group'>
                        <label>Role:</label>
                        <Select
                            options={roleOptions}
                            defaultValue={roleOptions[0]}
                            components={Animated}
                            name='role'
                        />
                    </fieldset>
                    <fieldset className='form-group'>
                        <label>Supportive Sets:</label>
                        <Select
                            options={setsOptions}
                            placeholder='Select sets your character has (only full sets please)...'
                            components={Animated}
                            name='sets[]'
                            isMulti
                        />
                    </fieldset>
                    <fieldset className='form-group text-right'>
                        <button className="btn btn-primary btn-lg" type="submit">Save</button>
                    </fieldset>
                </form>
            );
        }
    };
}

class CharacterList extends Component {
    render() {
        return (
            <ul>
                <li></li>
            </ul>
        );
    }
}

export { CharacterCreateForm, CharacterList };
