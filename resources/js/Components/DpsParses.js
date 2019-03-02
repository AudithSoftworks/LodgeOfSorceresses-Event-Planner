import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Component } from 'react';
import { Link } from "react-router-dom";
import Notification from '../Components/Notification';
import Axios from '../vendor/Axios';
import Loading from "./Loading";

library.add(faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus);

class DpsParses extends Component {
    constructor(props) {
        super(props);
        this.state = {
            character: null,
            parsesLoaded: false,
            dpsParses: [],
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();

        Axios.get('/api/chars/' + this.props.match.params.id, {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            this.cancelTokenSource = null;
            if (response.data) {
                this.setState({
                    character: response.data,
                    messages: [
                        {
                            type: "success",
                            message: "Character loaded."
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

        Axios.get('/api/chars/' + this.props.match.params.id + '/parses', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            this.cancelTokenSource = null;
            if (response.data) {
                this.setState({
                    parsesLoaded: true,
                    dpsParses: response.data.dpsParses,
                    messages: [
                        {
                            type: "success",
                            message: "Parses loaded."
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
        if (confirm('Are you sure you want to delete this parse?')) {
            this.cancelTokenSource = Axios.CancelToken.source();
            let currentTarget = event.currentTarget;
            const dpsParses = this.state.dpsParses;
            Axios.delete('/api/chars/' + this.props.match.params.id + '/parses/' + currentTarget.getAttribute('data-id'), {
                cancelToken: this.cancelTokenSource.token,
            }).then((response) => {
                this.cancelTokenSource = null;
                if (response.status === 204) {
                    dpsParses.forEach((item, idx) => {
                        if (item.id === parseInt(currentTarget.getAttribute('data-id'))) {
                            delete (dpsParses[idx]);
                        }
                    });
                    this.setState({
                        dpsParses: dpsParses,
                        messages: [
                            {
                                type: "success",
                                message: 'DPS Parse deleted.'
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

    renderList = (dpsParses, character) => {
        let parsesRendered = dpsParses.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set['id']} href={'https://eso-sets.com/set/' + set['slug']} className='badge badge-dark'>{set['name']}</a>);
                item.actionList = {
                    delete: <Link to='' onClick={this.handleDelete} data-id={item.id} title='Delete this Parse'><FontAwesomeIcon icon="trash-alt"/></Link>
                };
                let actionListRendered = [];
                for (const [actionType, link] of Object.entries(item.actionList)) {
                    actionListRendered.push(<li key={actionType}>{link}</li>);
                }

                return (
                    <tr key={'dpsParseRow-' + item.id}>
                        <td>
                            {characterSets.reduce((prev, curr) => [prev, ' ', curr])}
                        </td>
                        <td>
                            <a href={item['parse_file_hash']['large']} target='_blank'>
                                <img src={item['parse_file_hash']['thumbnail']} alt='Parse screenshot'/>
                            </a>
                        </td>
                        <td>
                            <a href={item['superstar_file_hash']['large']} target='_blank'>
                                <img src={item['superstar_file_hash']['thumbnail']} alt='Superstar screenshot'/>
                            </a>
                            <ul className='actionList'>{actionListRendered}</ul>
                        </td>

                    </tr>
                )
            }
        );
        if (parsesRendered.length) {
            parsesRendered = [
                <table key="character-list-table" className='pl-2 pr-2 col-md-24'>
                    <thead>
                        <tr>
                            <th width="70%">Sets</th>
                            <th width="15%">Parse Screenshot</th>
                            <th width="15%">Superstar Screenshot</th>
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>
            ];
        }

        const linkToDpsParseForm = <Link to={'/chars/' + this.props.match.params.id + '/parses/create'} className='ne-corner' title='Submit a Parse'><FontAwesomeIcon icon="user-plus"/></Link>;

        return [
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title col-md-24">Parses for <i>{character.name}</i></h2>
                {linkToDpsParseForm}
                {parsesRendered}
            </section>
        ];
    };

    render = () => {
        const {character, parsesLoaded, dpsParses, messages} = this.state;
        if (!parsesLoaded || !character) {
            return [
                <Loading key='loading'/>,
                <Notification key='notifications' messages={messages}/>
            ]
        }

        return [
            this.renderList(dpsParses, character),
            <Notification key='notifications' messages={messages}/>,
        ]
    };
}

export default DpsParses;
