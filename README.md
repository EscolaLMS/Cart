# Cart

## Model relation

The model user must be extended with the class HasCourses :
```
class User extends EscolaLms\Core\Models\User
{
    use CanTransaction;
```