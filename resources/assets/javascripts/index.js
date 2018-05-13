import '../sass/style.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import { CharacterCreateForm, CharacterList } from './Components/Characters';

ReactDOM.render(<CharacterCreateForm/>, document.getElementById('root'));
ReactDOM.render(<CharacterList/>, document.getElementById('root'));
