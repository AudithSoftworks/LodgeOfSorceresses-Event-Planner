import React from 'react';

class CharacterCreateForm extends React.Component {
    render() {
        return (
            <form method='POST' action='/chars'>
                <fieldset className='form-group'>
                    <label/>
                </fieldset>
            </form>
        );
    }
}

class CharacterList extends React.Component {
    render() {
        return (
            <form method='POST' action='/chars'>
                <fieldset className='form-group'>
                    <label/>
                </fieldset>
            </form>
        );
    }
}

export { CharacterCreateForm, CharacterList };
