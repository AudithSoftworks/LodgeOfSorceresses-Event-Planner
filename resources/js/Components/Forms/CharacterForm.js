import React, { Component } from 'react';
import { Link, Redirect } from "react-router-dom";
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';
import Axios from '../../vendor/Axios';
import Loading from "../Characters";
import Notification from '../Notification';

class CharacterForm extends Component {
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
            characterLoaded: null,
            characterProcessed: null,
            sets: null,
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();
        Axios.get('/api/sets', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            if (response.data) {
                this.setState({
                    setsLoaded: true,
                    sets: response.data.sets,
                    messages: [
                        {
                            type: "success",
                            message: "Form loaded."
                        }
                    ]
                });
                this.cancelTokenSource = null;
            }
        }).catch((error) => {
            if (!Axios.isCancel(error)) {
                this.setState({
                    messages: [
                        {
                            type: "danger",
                            message: error.response.data.message || error.response.statusText
                        }
                    ]
                })
            }
        });

        if (this.props.match.params.id) {
            const charId = this.props.match.params.id;
            Axios.get('/api/chars/' + charId + '/edit', {
                cancelToken: this.cancelTokenSource.token
            }).then((response) => {
                if (response.data) {
                    this.setState({
                        characterLoaded: response.data,
                    });
                    this.cancelTokenSource = null;
                }
            }).catch((error) => {
                if (!Axios.isCancel(error)) {
                    this.setState({
                        messages: [
                            {
                                type: "danger",
                                message: error.response.data.message || error.response.statusText
                            }
                        ]
                    })
                }
            });
        }
    };

    componentWillUnmount() {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    handleSubmit = (event) => {
        event.preventDefault();
        const data = new FormData(event.target);
        this.cancelTokenSource = Axios.CancelToken.source();
        let result = this.props.match.params.id
            ? Axios.post(
                '/api/chars/' + this.props.match.params.id, data, {
                    cancelToken: this.cancelTokenSource.token,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    }
                }
            )
            : Axios.post(
                '/api/chars', data, {
                    cancelToken: this.cancelTokenSource.token
                }
            );
        result.then((response) => {
            this.setState({
                characterProcessed: this.props.match.params.id ? response.status === 204 : response.status === 201,
                messages: [
                    {
                        type: "success",
                        message: response.statusText
                    }
                ]
            });
            this.cancelTokenSource = null;
        }).catch((error) => {
            if (!Axios.isCancel(error)) {
                this.setState({
                    messages: [
                        {
                            type: "danger",
                            message: error.response.data.message || error.response.statusText
                        }
                    ]
                })
            }
        });
    };

    renderForm = (sets) => {
        const setsOptions = Object.values(sets).map(
            item => ({value: item.id, label: item.name})
        );

        return (
            <form className='col-md-24 d-flex flex-row flex-wrap p-0' onSubmit={this.handleSubmit} key='characterCreationForm'>
                <h2 className="form-title col-md-24">{this.props.match.params.id ? 'Edit' : 'Create'} Character</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')}/>
                <fieldset className='form-group col-md-6'>
                    <label htmlFor='characterName'>Character Name:</label>
                    <input
                        type='text'
                        name='name'
                        id='characterName'
                        className='form-control form-control-md'
                        placeholder='Enter...'
                        defaultValue={this.state.characterLoaded ? this.state.characterLoaded.name : ''}
                        autoComplete='off'
                        required
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Class:</label>
                    <Select
                        options={this.classOptions}
                        defaultValue={
                            this.state.characterLoaded
                                ? this.classOptions.filter(option => option.value === this.state.characterLoaded.class)
                                : this.classOptions[0]
                        }
                        components={Animated}
                        name='class'
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Role:</label>
                    <Select
                        options={this.roleOptions}
                        defaultValue={
                            this.state.characterLoaded
                            ? this.roleOptions.filter(option => option.value === this.state.characterLoaded.role)
                            : this.roleOptions[0]
                        }
                        components={Animated}
                        name='role'
                    />
                </fieldset>
                <fieldset className='form-group col-md-6'>
                    <label>Supportive Sets:</label>
                    <Select
                        options={setsOptions}
                        defaultValue={
                            this.state.characterLoaded
                                ? setsOptions.filter(option => this.state.characterLoaded.sets.includes(option.value))
                                : null
                        }
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
        const {setsLoaded, characterLoaded, characterProcessed, sets, messages} = this.state;
        let formBasicsLoaded = setsLoaded && sets;
        let editFormCharacterLoaded = true;

        if (this.props.match.params.id) {
            if (!characterLoaded) {
                editFormCharacterLoaded = false;
            }
        }

        if (characterProcessed) {
            return <Redirect to="/chars"/>
        } else if (formBasicsLoaded && editFormCharacterLoaded) {
            return [
                this.renderForm(sets),
                <Notification key='notifications' messages={messages}/>
            ];
        }

        return <Loading/>;
    };
}

export default CharacterForm;
