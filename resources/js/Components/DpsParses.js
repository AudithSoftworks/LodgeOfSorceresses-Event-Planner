import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner, faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Component } from 'react';
import { Link } from "react-router-dom";
import Notification from '../Components/Notification';
import Axios from '../vendor/Axios';
import Loading from "./Loading";

library.add(faSpinner,faTachometerAlt, faTrashAlt, faUserEdit, faUserPlus);

class DpsParses extends Component {
    constructor(props) {
        super(props);
        this.state = {
            parsesLoaded: false,
            dpsParses: [],
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();
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

    renderList = (dpsParses) => {
        let parsesRendered = dpsParses.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set.id} href={'https://eso-sets.com/set/' + set.slug}>{set.name}</a>);
                console.log(item);
                item.actionList = {
                    delete: <Link to='' onClick={this.handleDelete} data-id={item.id}><FontAwesomeIcon icon="trash-alt"/></Link>
                };
                let actionListRendered = [];
                for (const [actionType, link] of Object.entries(item.actionList)) {
                    actionListRendered.push(<li key={actionType}>{link}</li>);
                }

                return (
                    <tr key={'dpsParseRow-' + item.id}>
                        <td>
                            {characterSets.reduce((prev, curr) => [prev, ', ', curr])}
                        </td>
                        <td><img src={item.parse_file_hash} alt='Parse screenshot' width='100' /></td>
                        <td><img src={item.superstar_file_hash} alt='Superstar screenshot' width='100' />
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
                            <th>Sets</th>
                            <th>Parse Screenshots</th>
                            <th>Superstar Screenshot</th>
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>
            ];
        }

        const linkToDpsParseForm = <Link to={'/chars/' + this.props.match.params.id + '/parses/create'}><FontAwesomeIcon icon="user-plus"/></Link>;

        return [
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title col-md-24">Parses {linkToDpsParseForm}</h2>
                {parsesRendered}
            </section>
        ];
    };

    render = () => {
        const {parsesLoaded, dpsParses, messages} = this.state;
        if (!parsesLoaded) {
            return [
                <Loading key='loading'/>,
                <Notification key='notifications' messages={messages}/>
            ]
        }

        return [
            this.renderList(dpsParses),
            <Notification key='notifications' messages={messages}/>,
        ]
    };
}

export default DpsParses;
