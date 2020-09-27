import FineUploaderTraditional from 'fine-uploader-wrappers';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import Loadable from 'react-loadable';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import postMyDpsParseAction from '../../actions/post-my-dps-parse';
import Loading from '../../Components/Loading';
import Notification from '../../Components/Notification';
import { characters } from '../../vendor/data';

const Gallery = new Loadable({
    loader: () => import(/* webpackPrefetch: true, webpackChunkName: "react-fine-uploader" */ 'react-fine-uploader'),
    loading() {
        return <Loading />;
    },
});

import(/* webpackPrefetch: true, webpackChunkName: "react-fine-uploader_gallery-css" */ '../../../sass/vendor/_fine-uploader-gallery.scss');

class DpsParseForm extends PureComponent {
    classOptions = [
        { value: 1, label: 'Dragonknight' },
        { value: 2, label: 'Nightblade' },
        { value: 3, label: 'Sorcerer' },
        { value: 4, label: 'Templar' },
        { value: 5, label: 'Warden' },
        { value: 6, label: 'Necromancer' },
    ];

    roleOptions = [
        { value: 1, label: 'Tank' },
        { value: 2, label: 'Healer' },
        { value: 3, label: 'Magicka DD' },
        { value: 4, label: 'Stamina DD' },
    ];

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

    infoScreenshotUploader = new FineUploaderTraditional({
        options: {
            request: {
                endpoint: '/api/files',
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                params: {
                    qqtag: 'info-screenshot',
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
                    tag: 'info-screenshot',
                },
                customHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            },
            callbacks: {
                onComplete: (id, name, responseJson) => {
                    this.setState({
                        infoScreenshotHash: responseJson.hash,
                    });
                },
                onDeleteComplete: (id, xhr, isError) => {
                    if (!isError) {
                        this.setState({
                            infoScreenshotHash: null,
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
            infoScreenshotHash: null,
        };
    }

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    }

    UNSAFE_componentWillUpdate = nextProps => {
        // We had a change in Characters data: Redirect!
        const { history, match } = this.props;
        const characterId = match.params.id;
        if (this.props.myCharacters !== nextProps.myCharacters) {
            return history.push('/@me/characters/' + characterId + '/parses');
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
        const { parseScreenshotHash, infoScreenshotHash } = this.state;
        const setsOptions = Object.values(sets).map(item => ({ value: item.id, label: item.name }));
        const charactersSetsIds = character ? Object.values(character.sets).map(set => set.id) : [];
        const animated = makeAnimated();

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="dpsParseForm">
                <h2 className="form-title col-md-24">Submit Parse for Character</h2>
                <article className="alert-info">
                    <b>Parse submission rules:</b>
                    <ul>
                        <li>Please use <strong>21-million-HP Trial Dummy</strong> for parsing and kill the dummy for 100% of its HP.</li>
                        <li><strong>Parses can&apos;t be older than 2 weeks!</strong> When a major Update is released, Parses need to be created after the launch date.</li>
                        <li>Make sure you have around <strong>5850 Penetration</strong> in your Parse-screenshots (±100 is an acceptable margin).</li>
                        <li>Every new Parse will update your existing Tier-level, i.e. sending lower DPS numbers can cause demotion.</li>
                        <li>When submitting a Parse, <strong>include only the Sets</strong> used for that particular Parse, removing everything else.</li>
                        <li><strong>Usage of Mythic set items in Parses is not allowed</strong>!</li>
                        <li>When submitting a Parse, <strong>enter DPS amount as it is, without rounding it</strong>, in the form of pure numbers.</li>
                        <li>Each Parse needs to have <strong>screenshots from both Fight-Log and Info screens of Combat Metrics addon</strong>.</li>
                        <li>After submitting a Parse, please check screenshots to see if they are readable;
                            if not, delete your Parse, resubmit with <strong>cropped screenshots</strong> for better readability.</li>
                        <li>Every Character needs their Parses <strong>refreshed at least every 60 days</strong>. Failing to do so will revoke your Tier-level automatically.</li>
                    </ul>
                </article>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <input type="hidden" name="parse_file_hash" value={parseScreenshotHash || ''} />
                <input type="hidden" name="info_file_hash" value={infoScreenshotHash || ''} />
                <fieldset className="form-group col-md-24 col-lg-12">
                    <label>Sets worn during Parse:</label>
                    <Select
                        options={setsOptions}
                        defaultValue={setsOptions.filter(option => charactersSetsIds.includes(option.value))}
                        placeholder="Full sets you have..."
                        components={animated}
                        name="sets[]"
                        isMulti
                    />
                </fieldset>
                <fieldset className="form-group col-md-8 col-lg-4">
                    <label>Class:</label>
                    <Select options={this.classOptions} defaultValue={this.classOptions.filter(option => option.label === character.class)} isDisabled={true} components={animated} name="class" />
                </fieldset>
                <fieldset className="form-group col-md-8 col-lg-4">
                    <label>Role:</label>
                    <Select options={this.roleOptions} defaultValue={this.roleOptions.filter(option => option.label === character.role)} isDisabled={true} components={animated} name="role" />
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
                    <label htmlFor="parseFile">Info Screenshot:</label>
                    <Gallery uploader={this.infoScreenshotUploader} className="uploader" />
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
        const { myCharacters } = this.props;
        if (!myCharacters) {
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
    sets: PropTypes.array,
    myCharacters: characters,
    notifications: PropTypes.array,
    parseScreenshotHash: PropTypes.string,
    infoScreenshotHash: PropTypes.string,

    postMyDpsParseAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
    sets: state.getIn(['sets']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postMyDpsParseAction: (characterId, data) => dispatch(postMyDpsParseAction(characterId, data)),
});

export default connect(mapStateToProps, mapDispatchToProps)(DpsParseForm);
