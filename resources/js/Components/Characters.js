import Axios from '../vendor/Axios';
import React, { Component } from 'react';
import { Link } from "react-router-dom";

class Characters extends Component {
    constructor(props) {
        super(props);
        this.state = {
            charactersLoaded: false,
            characters: [],
            error: null
        };
        this.render = this.render.bind(this);
    };

    componentDidMount = () => {
        Axios
            .get('/api/chars')
            .then((response) => {
                this.setState({
                    charactersLoaded: true,
                    chars: response.data.characters,
                    error: null
                });
            })
            .catch(function (error) {
                this.setState({
                    charactersLoaded: true,
                    chars: [],
                    error: error
                });
            });
    };

    renderList = (characters) => {
        let charactersRendered = characters.map(
            item => {
                const characterSets = item.sets.map(set => <a key={set.id} href={'https://eso-sets.com/set/' + set.slug}>{set.name}</a>);

                return (
                    <tr key={'characterRow-' + item.id}>
                        <td>{item.name}</td>
                        <td>{item.class}</td>
                        <td>{item.role}</td>
                        <td>{characterSets.reduce((prev, curr) => [prev, ', ', curr])}</td>
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

        const linkToCharacterCreateForm = <Link to="/chars/create">Add Character</Link>

        return (
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title font-green col-md-24">My Characters {linkToCharacterCreateForm}</h2>
                {charactersRendered}
            </section>
        );
    };

    render = () => {
        const {charactersLoaded, chars, error} = this.state;
        if (error) {
            return <fieldset className='error'>Error: {error}</fieldset>;
        } else if (!charactersLoaded) {
            return <fieldset className='general'>Loading...</fieldset>;
        }

        return this.renderList(chars);
    };
}

export default Characters;
