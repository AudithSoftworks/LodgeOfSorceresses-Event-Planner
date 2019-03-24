import FineUploaderTraditional from 'fine-uploader-wrappers';
import React, { PureComponent } from 'react';

import Loadable from 'react-loadable';
import { Link, Redirect } from "react-router-dom";
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';
import Axios from '../../vendor/Axios';
import Loading from "../Characters";
import Notification from '../Notification';

const Gallery = Loadable({
    loader: () => import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "react-fine-uploader" */
        'react-fine-uploader'),
    loading: () => <Loading/>
});

import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "react-fine-uploader_gallery-css" */
    '../../../sass/vendor/_fine-uploader-gallery.scss');

class DpsParseForm extends PureComponent {
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
    parseScreenshotUploader = new FineUploaderTraditional({
        options: {
            request: {
                endpoint: '/api/files',
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                params: {
                    qqtag: 'parse-screenshot'
                },
            },
            validation: {
                allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
                acceptFiles: 'image/*',
                itemLimit: 1
            },
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true
                },
                partSize: 1024000,
                success: {
                    endpoint: '/api/files?post-process=1'
                }
            },
            resume: {
                enabled: true,
                // recordsExpireIn: {{ config('filesystems.disks.local.chunks_expire_in') }}
                recordsExpireIn: 604800
            },
            deleteFile: {
                enabled: true,
                endpoint: '/api/files',
                params: {
                    tag: 'parse-screenshot'
                },
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            },
            callbacks: {
                onComplete: (id, name, responseJson) => {
                    this.setState({
                        parseScreenshotUploaded: responseJson.hash
                    });
                },
                onDeleteComplete: (id, xhr, isError) => {
                    if (!isError) {
                        this.setState({
                            parseScreenshotUploaded: null
                        });
                    }
                },
            }
        }
    });
    superstarScreenshotUploader = new FineUploaderTraditional({
        options: {
            request: {
                endpoint: '/api/files',
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                params: {
                    qqtag: 'superstar-screenshot'
                },
            },
            validation: {
                allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
                acceptFiles: 'image/*',
                itemLimit: 1
            },
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true
                },
                partSize: 1024000,
                success: {
                    endpoint: '/api/files?post-process=1'
                }
            },
            resume: {
                enabled: true,
                // recordsExpireIn: {{ config('filesystems.disks.local.chunks_expire_in') }}
                recordsExpireIn: 604800
            },
            deleteFile: {
                enabled: true,
                endpoint: '/api/files',
                params: {
                    tag: 'superstar-screenshot'
                },
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            },
            callbacks: {
                onComplete: (id, name, responseJson) => {
                    this.setState({
                        superstarScreenshotUploaded: responseJson.hash
                    });
                },
                onDeleteComplete: (id, xhr, isError) => {
                    if (!isError) {
                        this.setState({
                            superstarScreenshotUploaded: null
                        });
                    }
                },
            }
        }
    });

    constructor(props) {
        super(props);
        this.state = {
            dpsParseProcessed: null,
            characterLoaded: null,
            parseScreenshotUploaded: null,
            superstarScreenshotUploaded: null,
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
                            message: error.response.statusText
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
                                message: error.response.statusText
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
        let result = Axios.post(
            '/api/chars/' + this.props.match.params.id + '/parses', data, {
                cancelToken: this.cancelTokenSource.token
            }
        );
        result.then((response) => {
            this.setState({
                dpsParseProcessed: response.status === 201,
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
                const errorMessage = error.response.data;
                this.setState({
                    messages: Object.values(errorMessage.errors).map(item => {
                        return {type: 'danger', message: item[0]}
                    })
                });
            }
        });
    };

    renderForm = (sets) => {
        const setsOptions = Object.values(sets).map(
            item => ({value: item.id, label: item.name})
        );

        return (
            <form className='col-md-24 d-flex flex-row flex-wrap p-0' onSubmit={this.handleSubmit} key='dpsParseForm'>
                <h2 className="form-title col-md-24">Submit Parse for Character</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')}/>
                <input type="hidden" name="parse_file_hash" value={this.state.parseScreenshotUploaded || ''}/>
                <input type="hidden" name="superstar_file_hash" value={this.state.superstarScreenshotUploaded || ''}/>
                <fieldset className='form-group col-md-12'>
                    <label>Sets worn during Parse:</label>
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
                <fieldset className='form-group col-md-4'>
                    <label>Class:</label>
                    <Select
                        options={this.classOptions}
                        defaultValue={
                            this.state.characterLoaded
                                ? this.classOptions.filter(option => option.value === this.state.characterLoaded.class)
                                : this.classOptions[0]
                        }
                        isDisabled={true}
                        components={Animated}
                        name='class'
                    />
                </fieldset>
                <fieldset className='form-group col-md-4'>
                    <label>Role:</label>
                    <Select
                        options={this.roleOptions}
                        defaultValue={
                            this.state.characterLoaded
                                ? this.roleOptions.filter(option => option.value === this.state.characterLoaded.role)
                                : this.roleOptions[0]
                        }
                        isDisabled={true}
                        components={Animated}
                        name='role'
                    />
                </fieldset>
                <fieldset className='form-group col-md-4'>
                    <label htmlFor='dpsAmount'>DPS amount:</label>
                    <input
                        type='number'
                        name='dps_amount'
                        id='dpsAmount'
                        className='form-control form-control-md'
                        placeholder='Enter...'
                        autoComplete='off'
                        required
                    />
                </fieldset>

                <fieldset className='form-group col-md-12'>
                    <label htmlFor='parseFile'>Parse Screenshot:</label>
                    <Gallery uploader={this.parseScreenshotUploader} className='uploader'/>
                </fieldset>
                <fieldset className='form-group col-md-12'>
                    <label htmlFor='parseFile'>Superstar Screenshot:</label>
                    <Gallery uploader={this.superstarScreenshotUploader} className='uploader'/>
                </fieldset>
                <fieldset className='form-group col-md-24 text-right'>
                    <Link to={'/chars/' + this.props.match.params.id + '/parses'} className="btn btn-info btn-lg mr-1">Cancel</Link>
                    <button className="btn btn-primary btn-lg" type="submit">Save</button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const {characterLoaded, dpsParseProcessed, sets, messages} = this.state;
        if (dpsParseProcessed) {
            return <Redirect to={'/chars/' + this.props.match.params.id + '/parses'}/>
        } else if (sets && characterLoaded) {
            return [
                this.renderForm(sets),
                <Notification key='notifications' messages={messages}/>
            ];
        }

        return <Loading/>;
    };
}

export default DpsParseForm;
