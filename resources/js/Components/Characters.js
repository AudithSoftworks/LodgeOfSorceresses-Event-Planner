import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Fragment, PureComponent } from 'react';
import { Link, Redirect } from "react-router-dom";
import Notification from '../Components/Notification';
import Axios from '../vendor/Axios';
import Loading from "./Loading";

library.add(faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus);

class Characters extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            charactersLoaded: false,
            characters: [],
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();
        Axios.get('/api/chars', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            this.cancelTokenSource = null;
            if (response.data) {
                this.setState({
                    charactersLoaded: true,
                    characters: response.data.characters,
                    messages: [
                        {
                            type: "success",
                            message: "Characters loaded."
                        }
                    ]
                });
            }
        }).catch(error => {
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
            if (error.response && error.response.status === 403) {
                this.props.history.push('/', this.state);
            }
        });
    };

    componentWillUnmount() {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    handleDelete = (event) => {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this character?')) {
            this.cancelTokenSource = Axios.CancelToken.source();
            let currentTarget = event.currentTarget;
            const characters = this.state.characters;
            Axios.delete('/api/chars/' + currentTarget.getAttribute('data-id'), {
                cancelToken: this.cancelTokenSource.token
            }).then((response) => {
                this.cancelTokenSource = null;
                if (response.status === 204) {
                    characters.forEach((item, idx) => {
                        if (item.id === parseInt(currentTarget.getAttribute('data-id'))) {
                            delete (characters[idx]);
                        }
                    });
                    this.setState({
                        characters: characters,
                        messages: [
                            {
                                type: "success",
                                message: 'Character deleted.'
                            }
                        ],
                    });
                }
            }).catch(error => {
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

    renderList = (characters) => {
        let charactersRendered = characters.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className='badge badge-dark'>{set['name']}</a>);
                item.actionList = {
                    parses: item.role.indexOf('Damage') !== -1 ? <Link to={'/chars/' + item.id + '/parses'} title='Submit DPS Parse'><FontAwesomeIcon icon="tachometer-alt"/></Link> : null,
                    edit: <Link to={'/chars/' + item.id + '/edit'} title='Edit Character'><FontAwesomeIcon icon="user-edit"/></Link>,
                    delete: <Link to={'/api/chars/' + item.id} onClick={this.handleDelete} data-id={item.id} title='Delete Character'><FontAwesomeIcon icon="trash-alt"/></Link>
                };
                let actionListRendered = [];
                for (const [actionType, link] of Object.entries(item.actionList)) {
                    if (link) {
                        actionListRendered.push(<li key={actionType}>{link}</li>);
                    }
                }

                return (
                    <tr key={'characterRow-' + item.id} data-id={item.id}>
                        <td>{item.name}</td>
                        <td>{item.class}</td>
                        <td>{item.role}</td>
                        <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                        <td>
                            <ul className='actionList'>{actionListRendered}</ul>
                        </td>
                    </tr>
                )
            }
        );
        if (charactersRendered.length) {
            charactersRendered = [
                <table key="character-list-table" className='pl-2 pr-2 col-md-24'>
                    <thead>
                        <tr>
                            <th width="20%">Name</th>
                            <th width="10%">Class</th>
                            <th width="20%">Role</th>
                            <th width="40%">Sets</th>
                            <th width="10%"/>
                        </tr>
                    </thead>
                    <tbody>{charactersRendered}</tbody>
                </table>
            ];
        } else {
            const messages = [
                {
                    type: 'default',
                    message: [
                        <Fragment key='f-1'>Create a new character, by clicking</Fragment>,
                        <FontAwesomeIcon icon="user-plus" key='icon'/>,
                        <Fragment key='f-2'>icon on top right corner.</Fragment>
                    ].reduce((prev, curr) => [prev, ' ', curr])
                }
            ];
            const options = {
                container: 'bottom-center',
                animationIn: ["animated", "bounceInDown"],
                animationOut: ["animated", "bounceOutDown"],
                dismiss: {duration: 30000},
            };

            charactersRendered = [
                <Notification key='notifications' messages={messages} options={options}/>
            ];
        }

        const linkToCharacterCreateForm = <Link to="/chars/create" className='ne-corner' title='Submit a Character'><FontAwesomeIcon icon="user-plus"/></Link>;

        return [
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title col-md-24">My Characters</h2>
                {linkToCharacterCreateForm}
                {charactersRendered}
            </section>
        ];
    };

    render = () => {
        const {charactersLoaded, characters, messages} = this.state;
        const flashMessages = [];
        if (this.props.history && this.props.history.location.state && this.props.history.location.state.messages) {
            flashMessages.push(...this.props.history.location.state.messages);
        }

        if (charactersLoaded && this.props.match !== undefined && this.props.match.params.id === undefined) {
console.log(messages);
            return [
                this.renderList(characters),
                <Notification key='notifications' messages={messages}/>,
            ]
        }

        return [
            <Loading key='loading'/>,
            <Notification key='notifications' messages={flashMessages}/>
        ]
    };
}

export default Characters;
