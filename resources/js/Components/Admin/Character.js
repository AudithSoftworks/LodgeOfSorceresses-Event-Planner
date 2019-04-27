import { library } from "@fortawesome/fontawesome-svg-core";
import { faThList } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { PureComponent } from 'react';
import { Link } from "react-router-dom";
import axios from '../../vendor/axios';
import Loading from "../Loading";
import Notification from '../Notification';

library.add(faThList);

class Character extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            parsesLoaded: false,
            dpsParses: [],
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = axios.CancelToken.source();
        if (this.props.match.params.id) {
            const charId = this.props.match.params.id;
            axios.get('/api/admin/characters/' + charId, {
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
                                message: "Character loaded."
                            }
                        ]
                    });
                }
            }).catch(error => {
                if (!axios.isCancel(error)) {
                    this.setState({
                        messages: [
                            {
                                type: "danger",
                                message: error.response.data.message || error.response.statusText
                            }
                        ]
                    })
                }
                if (error.response && error.response.status > 400) {
                    this.props.history.push('/', this.state);
                }
            });
        }
    };

    componentWillUnmount() {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    handleDisapprove = (event) => {
        event.preventDefault();
        if (confirm('Are you sure you want to **disapprove** this parse?')) {
            let prompt = window.prompt('Please provide a reason for disapproval. This will be posted on Discord.');
            if (prompt && prompt.length) {
                this.cancelTokenSource = axios.CancelToken.source();
                let currentTarget = event.currentTarget;
                const dpsParses = this.state.dpsParses;
                axios.delete('/api/admin/parses/' + currentTarget.getAttribute('data-id'), {
                    cancelToken: this.cancelTokenSource.token,
                    data: {
                        reason_for_disapproval: prompt
                    }
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
                                    message: 'Parse disapproved.'
                                }
                            ],
                        });
                    }
                }).catch(error => {
                    if (!axios.isCancel(error)) {
                        this.setState({
                            messages: [
                                {
                                    type: "danger",
                                    message: error.response.data.message || error.response.statusText
                                }
                            ]
                        })
                    }
                    if (error.response) {
                        switch (error.response.status) {
                            case 403:
                                this.props.history.push('/', this.state);
                                break;
                            case 404:
                                this.props.history.push('/admin/parses', this.state);
                                break;
                        }
                    }
                });
            }
        }
    };

    renderList = (dpsParses) => {
        let parsesRendered = dpsParses.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set['id']} href={'https://eso-sets.com/set/' + set['id']} className='badge badge-dark'>{set['name']}</a>);

                return (
                    <tr key={'dpsParseRow-' + item.id}>
                        <td title={item.owner.name}>{item.owner.name}</td>
                        <td title={item.character.name}>
                            {item.character.name}<br/>
                            <small>{item.character.class} / {item.character.role}</small>
                        </td>
                        <td>{characterSets.reduce((prev, curr) => [prev, ' ', curr])}</td>
                        <td className='text-right'>{item['dps_amount']}</td>
                        <td className='text-right'>
                            <a href={item['parse_file_hash']['large']} target='_blank'>
                                <img src={item['parse_file_hash']['thumbnail']} alt='Parse screenshot'/>
                            </a>
                        </td>
                        <td className='text-right'>
                            <a href={item['superstar_file_hash']['large']} target='_blank'>
                                <img src={item['superstar_file_hash']['thumbnail']} alt='Superstar screenshot'/>
                            </a>
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
                            <th style={{width: '15%'}}>User</th>
                            <th style={{width: '20%'}}>Character</th>
                            <th style={{width: '25%'}}>Sets</th>
                            <th style={{width: '10%', textAlign: 'right'}}>DPS Number</th>
                            <th style={{width: '15%', textAlign: 'right'}}>Parse Screenshot</th>
                            <th style={{width: '15%', textAlign: 'right'}}>Superstar Screenshot</th>
                        </tr>
                    </thead>
                    <tbody>{parsesRendered}</tbody>
                </table>
            ];
        }

        const actionList = {
            return: <Link to={'/admin/characters'} title='Back to Character List'><FontAwesomeIcon icon={["far", "th-list"]}/></Link>,
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }

        return [
            <section className="col-md-24 p-0 mb-4" key='dpsParseList'>
                <h2 className="form-title col-md-24">Approved Parses for Selected Character</h2>
                <ul className='ne-corner'>{actionListRendered}</ul>
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

export default Character;
