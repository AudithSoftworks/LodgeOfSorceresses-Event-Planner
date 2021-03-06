import { schema } from 'normalizr';

export const user = new schema.Entity('user');
export const listOfUsers = new schema.Array(user);

export const set = new schema.Entity('sets');
export const listOfSets = new schema.Array(set);

export const skill = new schema.Entity('skills');
export const listOfSkills = new schema.Array(skill);

export const content = new schema.Entity('content');
export const listOfContent = new schema.Array(content);

export const character = new schema.Entity('characters');
export const listOfCharacters = new schema.Array(character);

export const dpsParse = new schema.Entity('dpsParses');
export const listOfDpsParses = new schema.Array(dpsParse);

export const team = new schema.Entity('teams');
export const listOfTeams = new schema.Array(team);

export const attendance = new schema.Entity('attendance');
export const listOfAttendances = new schema.Array(attendance);
