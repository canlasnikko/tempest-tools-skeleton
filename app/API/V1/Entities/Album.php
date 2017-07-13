<?php
namespace App\API\V1\Entities;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TempestTools\Crud\Laravel\EntityAbstract;

/** @noinspection LongInheritanceChainInspection */

/**
 * @ORM\Entity(repositoryClass="App\API\V1\Repositories\AlbumRepository")
 * @ORM\Table(indexes={@ORM\Index(name="name_idx", columns={"name"}),@ORM\Index(name="releaseDate_idx", columns={"release_date"})})
 * @ORM\HasLifecycleCallbacks
 */
class Album extends EntityAbstract
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="release_date")
     */
    private $releaseDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\API\V1\Entities\Artist", inversedBy="albums", cascade={"persist"})
     * @ORM\JoinColumn(name="artist_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $artist;

    /**
     * @ORM\ManyToMany(targetEntity="App\API\V1\Entities\User", mappedBy="albums")
     * 
     */
    private $users;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        parent::__construct();
    }


    /**
     * @param User $user
     * @param bool $preventLoop
     * @return Album
     */
    public function addUser(User $user, bool $preventLoop = false): Album
    {
        if ($preventLoop === false) {
            $user->addAlbum($this, true);
        }

        $this->users[] = $user;
        return $this;
    }

    /**
     * @param User $user
     * @param bool $preventLoop
     * @return Album
     */
    public function removeUser(User $user, bool $preventLoop = false): Album
    {
        if ($preventLoop === false) {
            $user->removeAlbum($this, true);
        }
        $this->users->removeElement($user);
        return $this;
    }

    /**
     * @return Artist
     */
    public function getArtist(): Artist
    {
        return $this->artist;
    }

    /**
     * @param Artist $artist
     * @return Album
     */
    public function setArtist(Artist $artist): Album
    {
        $this->artist = $artist;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReleaseDate():DateTime
    {
        return $this->releaseDate;
    }

    /**
     * @param DateTime $releaseDate
     * @return Album
     */
    public function setReleaseDate(DateTime $releaseDate): Album
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Album
     */
    public function setName(string $name): Album
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getId():int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Album
     */
    public function setId(int $id): Album
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getUsers(): ?Collection
    {
        return $this->users;
    }


    /**
     * @return array
     */
    public function getTTConfig(): array
    {
        return [
            'default'=>[
                'create'=>[
                    'allowed'=>false, // by default this is not allowed
                    'validate'=>[ // Add a validator that will be inherited by all other configs
                        'rules'=>[
                            'name'=>'required|min:2',
                            'releaseDate'=>'required|date'
                        ],
                        'messages'=>NULL,
                        'customAttributes'=>NULL,
                    ],
                ],
                'update'=>[ // Same as default create
                    'extends'=>[':default:create']
                ],
                'delete'=>[ // Same as default create
                    'extends'=>[':default:create']
                ]
            ],
            'user'=>[
                'create'=>[
                    'extends'=>[':default:create'],
                    'allowed'=>false, // users can't allowed to create
                    'permissive'=>false, // the following rules are defined here in order to be inherited further down
                    'fields'=>[
                        'users'=>[ // when this is inherited a user will be able to add them selves to an album
                            'permissive'=>false,
                            'enforce'=>[
                                'id'=>':userEntity:id' // When adding them selves to an album we enforce that the user is assigning their own user entity to the album
                            ],
                            'assign'=>[ // the can only add and remove them selves from an album
                                'add'=>true,
                                'remove'=>true,
                            ],
                            'chain'=>[
                                'read'=>true // Then can only add existing users, they can not create update or delete users in the process
                            ]
                        ]
                    ],
                ],
                'update'=>[
                    'extends'=>[':user:create'], // The same as create but it's allowed this time
                    'allowed'=>true,
                ],
                'delete'=>[
                    'extends'=>[':user:create']
                ],
            ],
            'admin'=>[ // Extends default because default has no additional rules on it, so super admins can do anything
                'create'=>[
                    'extends'=>[':default:create'],
                    'allowed'=>true
                ],
                'update'=>[
                    'extends'=>[':default:create'],
                    'allowed'=>true
                ],
                'delete'=>[
                    'extends'=>[':default:create'],
                    'allowed'=>true
                ],
            ],
            'testPermissive1'=>[
                'create'=>[
                    'permissive'=>true, // the following rules are defined here in order to be inherited further down
                    'fields'=>[
                        'name'=>[
                            'permissive'=>false,
                        ]
                    ]
                ]
            ],
            'testPermissive2'=>[
                'create'=>[
                    'permissive'=>true, // the following rules are defined here in order to be inherited further down
                    'fields'=>[
                        'name'=>[
                            'permissive'=>false,
                            'assign'=>[ // the can only add and remove them selves from an album
                                'set'=>true
                            ],
                        ]
                    ]
                ]
            ],
            'testFastMode1'=>[
                'create'=>[
                    'fastMode'=>true, // the following rules are defined here in order to be inherited further down
                    'fields'=>[
                        'name'=>[
                            'setTo'=>'foo'
                        ]
                    ]
                ]
            ],
            'testFastMode2'=>[
                'create'=>[
                    'fastMode'=>true, // the following rules are defined here in order to be inherited further down
                    'fields'=>[
                        'name'=>[
                            'fastMode'=>false,
                            'setTo'=>'foo'
                        ]
                    ]
                ]
            ],
            'testTopLevelSetToAndMutate'=>[
                'create'=>[
                    'setTo'=>[
                        'name'=>'foo',
                    ],
                    'mutate'=>function ($target, $extra) {
                        /** @var Album $self */
                        $self = $extra['self'];
                        $currentName = $self->getName();
                        $newName = $currentName . 'bar';
                        $self->setName($newName);
                    }
                ]
            ],
            'testEnforceTopLevelWorks'=>[
                'create'=>[
                    'enforce'=>[
                        'name'=>'NOT BEETHOVEN'
                    ]
                ]
            ],
            'testTopLevelClosure'=>[
                'create'=>[
                    'closure'=>function ($target, $extra) {
                        return false;
                    }
                ]
            ],
            'testLowLevelEnforce'=>[
                'create'=>[
                    'fields'=>[
                        'name'=>[
                            'enforce'=>'foo'
                        ]
                    ]
                ]
            ],
            'testLowLevelEnforceOnRelation'=>[
                'create'=>[
                    'fields'=>[
                        'artist'=>[
                            'enforce'=>[
                                'name'=>'Bob the artist'
                            ]
                        ]
                    ]
                ]
            ],
            'testLowLevelClosure'=>[
                'create'=>[
                    'fields'=>[
                        'name'=>[
                            'closure'=>function ($target, $extra) {
                                return false;
                            }
                        ]
                    ]
                ]
            ],
            'testLowLevelMutate'=>[
                'create'=>[
                    'fields'=>[
                        'name'=>[
                            'mutate'=>function ($target, $extra) {
                                return 'foobar';
                            }
                        ]
                    ]
                ]
            ],
        ];


    }

}