import Axios from '../vendor/Axios';
import React from 'react';

class CharacterCreateForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            setsLoaded: false,
            sets: [],
            error: null
        };
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit(event) {
        event.preventDefault();
        const data = new FormData(event.target);
        console.log(event.target);
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
    }

    componentDidMount() {
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
    }

    render() {
        const {setsLoaded, sets, error} = this.state;
        if (error) {
            return <fieldset className='error'>Error</fieldset>;
        } else if (!setsLoaded) {
            return <fieldset className='general'>Loading</fieldset>;
        } else {
            const setsNode = Object.values(sets).map(
                item => (
                    <option key={item.name} value={item.id}>{item.name}</option>
                )
            );
            return (
                <form className='col-md-24' onSubmit={this.handleSubmit}>
                    <h2 className="form-title font-green col-md-24">Create Character</h2>
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')}/>
                    <fieldset className='form-group'>
                        <label htmlFor='characterName'>Character Name:</label>
                        <input type='text' name='name' className='form-control form-control-lg' id='characterName' aria-describedby='characterNameHelp' placeholder='Enter character name'/>
                        <small id='characterNameHelp' className='form-text text-muted'>Enter character name.</small>
                    </fieldset>
                    <fieldset className='form-group'>
                        <label htmlFor='characterClass'>Class:</label>
                        <select name='class' className='form-control form-control-lg' id='characterClass' aria-describedby='characterClassHelp'>
                            <option value='1'>Dragonknight</option>
                            <option value='2'>Nightblade</option>
                            <option value='3'>Sorcerer</option>
                            <option value='4'>Templar</option>
                            <option value='5'>Warden</option>
                        </select>
                        <small id='characterClassHelp' className='form-text text-muted'>Select character class.</small>
                    </fieldset>
                    <fieldset className='form-group'>
                        <label htmlFor='characterRole'>Role:</label>
                        <select name='role' className='form-control form-control-lg' id='characterRole' aria-describedby='characterRoleHelp'>
                            <option value='1'>Tank</option>
                            <option value='2'>Healer</option>
                            <option value='3'>Damage Dealer (Magicka)</option>
                            <option value='4'>Damage Dealer (Stamina)</option>
                        </select>
                        <small id='characterRoleHelp' className='form-text text-muted'>Select character role.</small>
                    </fieldset>
                    <fieldset className='form-group'>
                        <label htmlFor='characterSets'>Supportive Sets:</label>
                        <select name='sets[]' className='form-control form-control-lg' id='characterSets' aria-describedby='characterSetsHelp' multiple>
                            {setsNode}
                        </select>
                        <small id='characterSetsHelp' className='form-text text-muted'>Select sets your character has (only full sets please). Hold down Ctrl key (Cmd key on Mac) to select more than
                            one.
                        </small>
                    </fieldset>
                    <fieldset className='form-group text-right'>
                        <button className="btn btn-primary btn-lg mr-1" type="reset">Reset</button>
                        <button className="btn btn-primary btn-lg" type="submit">Save</button>
                    </fieldset>
                </form>
            );
        }
    }
}

class CharacterList extends React.Component {
    render() {
        return (
            <form method='POST' action='/chars'>
                <fieldset className='form-group'>
                    <label/>
                </fieldset>
            </form>
        );
    }
}

export { CharacterCreateForm, CharacterList };
