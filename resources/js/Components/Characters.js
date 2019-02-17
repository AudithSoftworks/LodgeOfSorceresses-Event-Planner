import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Component } from 'react';
import { Link } from "react-router-dom";
import Notification from '../Components/Notification';
import Axios from '../vendor/Axios';
import Loading from "./Loading";

library.add(faSpinner,faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus);

class Characters extends Component {
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
                            message: error.response.statusText
                        }
                    ]
                })
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
                if (response.data.success === true) {
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
                                message: response.statusText
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
                                message: error.response.statusText
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
                const characterSets = item.sets.map(set => <a key={set.id} href={'https://eso-sets.com/set/' + set.slug}>{set.name}</a>);
                item.actionList = {
                    parseCreate: <Link to={'/chars/' + item.id + '/parses/create'}><FontAwesomeIcon icon="tachometer-alt"/></Link>,
                    edit: <Link to={'/chars/' + item.id + '/edit'}><FontAwesomeIcon icon="user-edit"/></Link>,
                    delete: <Link to={'/api/chars/' + item.id} onClick={this.handleDelete} data-id={item.id}><FontAwesomeIcon icon="trash-alt"/></Link>
                };
                let actionListRendered = [];
                for (const [actionType, link] of Object.entries(item.actionList)) {
                    actionListRendered.push(<li key={actionType}>{link}</li>);
                }

                return (
                    <tr key={'characterRow-' + item.id} data-id={item.id}>
                        <td>{item.name}</td>
                        <td>{item.class}</td>
                        <td>{item.role}</td>
                        <td>
                            {characterSets.reduce((prev, curr) => [prev, ', ', curr])}
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
                            <th>Name</th>
                            <th>Class</th>
                            <th>Role</th>
                            <th>Sets</th>
                        </tr>
                    </thead>
                    <tbody>{charactersRendered}</tbody>
                </table>
            ];
        }

        const linkToCharacterCreateForm = <Link to="/chars/create"><FontAwesomeIcon icon="user-plus"/></Link>;

        return [
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title col-md-24">My Characters {linkToCharacterCreateForm}</h2>
                {charactersRendered}
            </section>
        ];
    };

    render = () => {
        const {charactersLoaded, characters, messages} = this.state;
        if (!charactersLoaded) {
            return [
                <Loading key='loading'/>,
                <Notification key='notifications' messages={messages}/>
            ]
        }

        return [
            this.renderList(characters),
            <Notification key='notifications' messages={messages}/>,
        ]
    };
}

export default Characters;
