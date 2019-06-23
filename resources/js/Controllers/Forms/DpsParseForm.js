import FineUploaderTraditional from 'fine-uploader-wrappers';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import Loadable from 'react-loadable';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import * as Animated from 'react-select/lib/animated';
import postMyDpsParseAction from '../../actions/post-my-dps-parse';
import putMyDpsParseAction from '../../actions/put-my-dps-parse';
import { characters, user } from '../../vendor/data';
import Loading from '../../Components/Loading';
import Notification from '../../Components/Notification';

const Gallery = Loadable({
    loader: () =>
        import(
            /* webpackPrefetch: true */
            /* webpackChunkName: "react-fine-uploader" */
            'react-fine-uploader'
        ),
    loading: () => <Loading />,
});

import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "react-fine-uploader_gallery-css" */
    '../../../sass/vendor/_fine-uploader-gallery.scss'
);

class DpsParseForm extends PureComponent {
    classOptions = [
        { value: 1, label: 'Dragonknight' },
        { value: 2, label: 'Nightblade' },
        { value: 3, label: 'Sorcerer' },
        { value: 4, label: 'Templar' },
        { value: 5, label: 'Warden' },
        { value: 6, label: 'Necromancer' },
    ];
    roleOptions = [{ value: 1, label: 'Tank' }, { value: 2, label: 'Healer' }, { value: 3, label: 'Damage Dealer (Magicka)' }, { value: 4, label: 'Damage Dealer (Stamina)' }];
    parseScreenshotUploader = new FineUploaderTraditional({
        options: {
            request: {
                endpoint: '/api/files',
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                params: {
                    qqtag: 'parse-screenshot',
                },
            },
            validation: {
                allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
                acceptFiles: 'image/*',
                itemLimit: 1,
            },
            scaling: {
                sendOriginal: false,
                includeExif: true,
                sizes: [{ name: '', maxSize: 1024 }],
            },
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true,
                },
                partSize: 1024000,
                success: {
                    endpoint: '/api/files?post-process=1',
                },
            },
            resume: {
                enabled: true,
                // recordsExpireIn: {{ config('filesystems.disks.local.chunks_expire_in') }}
                recordsExpireIn: 604800,
            },
            deleteFile: {
                enabled: true,
                endpoint: '/api/files',
                params: {
                    tag: 'parse-screenshot',
                },
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            },
            callbacks: {
                onComplete: (id, name, responseJson) => {
                    this.setState({
                        parseScreenshotHash: responseJson.hash,
                    });
                },
                onDeleteComplete: (id, xhr, isError) => {
                    if (!isError) {
                        this.setState({
                            parseScreenshotHash: null,
                        });
                    }
                },
            },
        },
    });
    superstarScreenshotUploader = new FineUploaderTraditional({
        options: {
            request: {
                endpoint: '/api/files',
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                params: {
                    qqtag: 'superstar-screenshot',
                },
            },
            validation: {
                allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
                acceptFiles: 'image/*',
                itemLimit: 1,
            },
            scaling: {
                sendOriginal: false,
                includeExif: true,
                sizes: [{ name: '', maxSize: 1024 }],
            },
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true,
                },
                partSize: 1024000,
                success: {
                    endpoint: '/api/files?post-process=1',
                },
            },
            resume: {
                enabled: true,
                // recordsExpireIn: {{ config('filesystems.disks.local.chunks_expire_in') }}
                recordsExpireIn: 604800,
            },
            deleteFile: {
                enabled: true,
                endpoint: '/api/files',
                params: {
                    tag: 'superstar-screenshot',
                },
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            },
            callbacks: {
                onComplete: (id, name, responseJson) => {
                    this.setState({
                        superstarScreenshotHash: responseJson.hash,
                    });
                },
                onDeleteComplete: (id, xhr, isError) => {
                    if (!isError) {
                        this.setState({
                            superstarScreenshotHash: null,
                        });
                    }
                },
            },
        },
    });

    constructor(props) {
        super(props);
        this.state = {
            parseScreenshotHash: null,
            superstarScreenshotHash: null,
        };
    }

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    }

    componentWillUpdate = nextProps => {
        // We had a change in Characters data: Redirect!
        const { match } = this.props;
        const characterId = match.params.id;
        if (this.props.myCharacters !== nextProps.myCharacters) {
            return this.props.history.push('/@me/characters/' + characterId + '/parses');
        }
    };

    getCharacter = () => {
        const { match, myCharacters } = this.props;
        const characterId = match.params.id;

        return myCharacters.find(item => item.id === parseInt(characterId));
    };

    handleSubmit = event => {
        event.preventDefault();
        const { match, postMyDpsParseAction } = this.props;
        const characterId = match.params.id;
        const data = new FormData(event.target);

        return postMyDpsParseAction(characterId, data);
    };

    renderForm = character => {
        const { sets } = this.props;
        const { parseScreenshotHash, superstarScreenshotHash } = this.state;
        const setsOptions = Object.values(sets).map(item => ({ value: item.id, label: item.name }));
        const charactersSetsIds = character ? Object.values(character.sets).map(item => item.id) : [];

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="dpsParseForm">
                <h2 className="form-title col-md-24">Submit Parse for Character</h2>
                <article className="alert-info">
                    <b>Usage tips:</b>
                    <ul>
                        <li>Every new parse will renew your clearance level, i.e. sending lower DPS numbers can revoke its clearance and demote your account.</li>
                        <li>When creating a Parse, include only the sets used for that particular parse, removing everything else.</li>
                        <li>Each Parse needs to have both Combat Metrics and Superstar addon screenshots.</li>
                        <li>
                            Use the exact setup you will be using in Trials. And use reasonable food/potion you would use in Trials. E.g.: the usage of food that has no HP-buff to it (a case which in
                            most cases wouldn't be realistic to Trial conditions) is not allowed! Unless your Base HP is within acceptable raiding ranges of course. Yes, we realize this kinda
                            scenarios can be tricky, so we will also check your Superstar screenshots for Base-HP and other values, overall to decide whether the certain Parse looks cheesy or not.
                        </li>
                        <li>Every Character needs to send a fresh Parse every 15 days, latest. Failing to do so will revoke your Clearance automatically.</li>
                    </ul>
                </article>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <input type="hidden" name="parse_file_hash" value={parseScreenshotHash || ''} />
                <input type="hidden" name="superstar_file_hash" value={superstarScreenshotHash || ''} />
                <fieldset className="form-group col-md-24 col-lg-12">
                    <label>Sets worn during Parse:</label>
                    <Select
                        options={setsOptions}
                        defaultValue={setsOptions.filter(option => charactersSetsIds.includes(option.value))}
                        placeholder="Full sets you have..."
                        components={Animated}
                        name="sets[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-lg-4">
                    <label>Class:</label>
                    <Select options={this.classOptions} defaultValue={this.classOptions.filter(option => option.label === character.class)} isDisabled={true} components={Animated} name="class" />
                </fieldset>
                <fieldset className="form-group col-md-8 col-lg-4">
                    <label>Role:</label>
                    <Select options={this.roleOptions} defaultValue={this.roleOptions.filter(option => option.label === character.role)} isDisabled={true} components={Animated} name="role" />
                </fieldset>
                <fieldset className="form-group col-md-8 col-lg-4">
                    <label htmlFor="dpsAmount">DPS amount:</label>
                    <input type="number" name="dps_amount" id="dpsAmount" className="form-control form-control-md" placeholder="Enter..." autoComplete="off" required />
                </fieldset>

                <fieldset className="form-group col-md-12">
                    <label htmlFor="parseFile">Parse Screenshot:</label>
                    <Gallery uploader={this.parseScreenshotUploader} className="uploader" />
                </fieldset>
                <fieldset className="form-group col-md-12">
                    <label htmlFor="parseFile">Superstar Screenshot:</label>
                    <Gallery uploader={this.superstarScreenshotUploader} className="uploader" />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <Link to={'/@me/characters/' + character.id + '/parses'} className="btn btn-info btn-lg mr-1">
                        Cancel
                    </Link>
                    <button className="btn btn-primary btn-lg" type="submit">
                        Save
                    </button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { me } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const character = this.getCharacter();
        if (!character) {
            return <Redirect to="/@me/characters" />;
        }

        return [this.renderForm(character), <Notification key="notifications" />];
    };
}

DpsParseForm.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    sets: PropTypes.array,
    myCharacters: characters,
    notifications: PropTypes.array,
    parseScreenshotHash: PropTypes.string,
    superstarScreenshotHash: PropTypes.string,

    postMyDpsParseAction: PropTypes.func.isRequired,
    putMyDpsParseAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    sets: state.getIn(['sets']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postMyDpsParseAction: (characterId, data) => dispatch(postMyDpsParseAction(characterId, data)),
    putMyDpsParseAction: (characterId, parseId, data) => dispatch(putMyDpsParseAction(characterId, parseId, data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(DpsParseForm);
